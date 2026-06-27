<?php

namespace App\Jobs;

use App\Models\RecordingExport;
use App\Services\AudioConverter;
use App\Services\RecordingExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessRecordingExport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(
        public RecordingExport $export,
    ) {}

    public function handle(AudioConverter $converter): void
    {
        $this->export->refresh()->load('recording');

        if ($this->export->status === RecordingExport::STATUS_COMPLETED) {
            return;
        }

        $this->export->update([
            'status' => RecordingExport::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        try {
            $recording = $this->export->recording;

            if (! $recording) {
                throw new \RuntimeException('Recording no longer exists.');
            }

            $sourcePath = storage_path('app/public/' . $recording->file_path);
            $relativeOutput = RecordingExportService::exportStoragePath($this->export);

            $converter->convertToStorage($sourcePath, $this->export->format, $relativeOutput);

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
        }
    }
}
