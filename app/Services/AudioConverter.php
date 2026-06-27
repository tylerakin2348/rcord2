<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class AudioConverter
{
    public function isAvailable(): bool
    {
        return $this->binaryWorks($this->binary());
    }

    public function supportedFormats(): array
    {
        return ['mp3', 'wav'];
    }

    public function assertFormat(string $format): void
    {
        if (! in_array($format, $this->supportedFormats(), true)) {
            throw new RuntimeException("Unsupported export format: {$format}");
        }
    }

    /**
     * Convert a source file to a temporary output path.
     */
    public function convertToTempFile(string $absoluteInputPath, string $format): string
    {
        $this->assertFormat($format);

        if (! is_file($absoluteInputPath)) {
            throw new RuntimeException('Source audio file was not found.');
        }

        if (! $this->isAvailable()) {
            throw new RuntimeException('Audio conversion is unavailable. FFmpeg is required on the server.');
        }

        $tempOutput = tempnam(sys_get_temp_dir(), 'rcord_export_');
        if ($tempOutput === false) {
            throw new RuntimeException('Unable to create temporary export file.');
        }

        $tempOutputWithExt = $tempOutput . '.' . $format;
        rename($tempOutput, $tempOutputWithExt);

        $this->runConversion($absoluteInputPath, $tempOutputWithExt, $format);

        return $tempOutputWithExt;
    }

    /**
     * Convert a source audio file and store the result on the local disk.
     *
     * @return array{relative_path: string, absolute_path: string}
     */
    public function convertToStorage(string $absoluteInputPath, string $format, string $relativeOutputPath): array
    {
        $this->assertFormat($format);

        if (! is_file($absoluteInputPath)) {
            throw new RuntimeException('Source audio file was not found.');
        }

        if (! $this->isAvailable()) {
            throw new RuntimeException('Audio conversion is unavailable. FFmpeg is required on the server.');
        }

        $tempOutput = tempnam(sys_get_temp_dir(), 'rcord_export_');
        if ($tempOutput === false) {
            throw new RuntimeException('Unable to create temporary export file.');
        }

        $tempOutputWithExt = $tempOutput . '.' . $format;
        rename($tempOutput, $tempOutputWithExt);

        try {
            $this->runConversion($absoluteInputPath, $tempOutputWithExt, $format);

            $absoluteOutput = storage_path('app/private/' . $relativeOutputPath);
            $directory = dirname($absoluteOutput);
            if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new RuntimeException('Unable to create export directory.');
            }

            if (! rename($tempOutputWithExt, $absoluteOutput)) {
                copy($tempOutputWithExt, $absoluteOutput);
            }
        } finally {
            @unlink($tempOutputWithExt);
        }

        return [
            'relative_path' => $relativeOutputPath,
            'absolute_path' => $absoluteOutput,
        ];
    }

    private function runConversion(string $inputPath, string $outputPath, string $format): void
    {
        $command = match ($format) {
            'mp3' => [
                $this->binary(),
                '-y',
                '-i', $inputPath,
                '-vn',
                '-codec:a', 'libmp3lame',
                '-qscale:a', '2',
                $outputPath,
            ],
            'wav' => [
                $this->binary(),
                '-y',
                '-i', $inputPath,
                '-vn',
                '-codec:a', 'pcm_s16le',
                $outputPath,
            ],
            default => throw new RuntimeException("Unsupported export format: {$format}"),
        };

        $process = new Process($command);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'Audio conversion failed.');
        }

        if (! is_file($outputPath) || filesize($outputPath) === 0) {
            throw new RuntimeException('Audio conversion produced an empty file.');
        }
    }

    public function makeDownloadFilename(string $baseName, string $format, bool $isZip = false): string
    {
        $safeBase = Str::slug($baseName) ?: 'recording';

        if ($isZip) {
            return $safeBase . '.zip';
        }

        return $safeBase . '.' . $format;
    }

    private function binary(): string
    {
        $configured = config('services.ffmpeg.path', 'ffmpeg');

        if ($configured !== 'ffmpeg' && $this->binaryWorks($configured)) {
            return $configured;
        }

        if ($this->binaryWorks('ffmpeg')) {
            return 'ffmpeg';
        }

        foreach ($this->candidateBinaries() as $candidate) {
            if ($this->binaryWorks($candidate)) {
                return $candidate;
            }
        }

        return $configured;
    }

    /**
     * @return list<string>
     */
    private function candidateBinaries(): array
    {
        return array_values(array_unique(array_filter([
            '/opt/homebrew/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/usr/bin/ffmpeg',
        ])));
    }

    private function binaryWorks(string $binary): bool
    {
        $process = new Process([$binary, '-version']);
        $process->run();

        return $process->isSuccessful();
    }
}
