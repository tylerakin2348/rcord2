<?php

namespace App\Jobs;

use App\Models\RecordingExport;
use App\Services\AudioConverter;
use App\Services\RecordingExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class ProcessSessionExport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function __construct(
        public RecordingExport $export,
    ) {}

    public function handle(AudioConverter $converter): void
    {
        $this->export->refresh()->load([
            'recordingSession.recordings' => function ($query) {
                $query->orderBy('loop_number')->orderBy('created_at');
            },
        ]);

        if ($this->export->status === RecordingExport::STATUS_COMPLETED) {
            return;
        }

        $this->export->update([
            'status' => RecordingExport::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        $tempFiles = [];

        try {
            $session = $this->export->recordingSession;

            if (! $session || $session->recordings->isEmpty()) {
                throw new \RuntimeException('Session has no recordings to export.');
            }

            $zipPath = tempnam(sys_get_temp_dir(), 'rcord_session_export_');
            if ($zipPath === false) {
                throw new \RuntimeException('Unable to create temporary zip file.');
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to open zip archive.');
            }

            $addedFiles = 0;

            foreach ($session->recordings as $recording) {
                $sourcePath = storage_path('app/public/' . $recording->file_path);

                if (! is_file($sourcePath)) {
                    continue;
                }

                $tempConvertedWithExt = $converter->convertToTempFile($sourcePath, $this->export->format);
                $tempFiles[] = $tempConvertedWithExt;

                $entryName = $recording->loop_number
                    ? sprintf('Loop %d.%s', $recording->loop_number, $this->export->format)
                    : pathinfo($recording->filename, PATHINFO_FILENAME) . '.' . $this->export->format;

                $zip->addFile($tempConvertedWithExt, $entryName);
                $addedFiles++;
            }

            $zip->close();

            if ($addedFiles === 0) {
                throw new \RuntimeException('No recording files were found on disk.');
            }

            $relativeOutput = RecordingExportService::exportStoragePath($this->export, 'zip');
            $absoluteOutput = storage_path('app/private/' . $relativeOutput);
            $directory = dirname($absoluteOutput);

            if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                throw new \RuntimeException('Unable to create export directory.');
            }

            if (! rename($zipPath, $absoluteOutput)) {
                copy($zipPath, $absoluteOutput);
            }

            @unlink($zipPath);
            unset($zipPath);

            $this->export->update([
                'status' => RecordingExport::STATUS_COMPLETED,
                'file_path' => $relativeOutput,
                'expires_at' => now()->addDay(),
            ]);
        } catch (Throwable $exception) {
            if ($this->export->file_path) {
                Storage::disk('local')->delete($this->export->file_path);
            }

            $this->export->update([
                'status' => RecordingExport::STATUS_FAILED,
                'file_path' => null,
                'error_message' => $exception->getMessage(),
            ]);
        } finally {
            foreach ($tempFiles as $tempFile) {
                @unlink($tempFile);
            }

            if (isset($zipPath) && is_file($zipPath)) {
                @unlink($zipPath);
            }
        }
    }
}
