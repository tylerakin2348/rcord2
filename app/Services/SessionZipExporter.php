<?php

namespace App\Services;

use App\Models\RecordingSession;
use Illuminate\Support\Str;
use ZipArchive;

class SessionZipExporter
{
    /**
     * Build a zip archive for a session's recordings.
     * Extracted to a service so a queued job can reuse this later if needed.
     */
    public function export(RecordingSession $session): array
    {
        $session->load(['recordings' => function ($query) {
            $query->orderBy('loop_number')->orderBy('created_at');
        }]);

        if ($session->recordings->isEmpty()) {
            throw new \RuntimeException('Session has no recordings to download.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'rcord_session_');
        if ($zipPath === false) {
            throw new \RuntimeException('Unable to create temporary zip file.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            throw new \RuntimeException('Unable to open zip archive.');
        }

        $addedFiles = 0;

        foreach ($session->recordings as $recording) {
            $absolutePath = storage_path('app/public/' . $recording->file_path);

            if (! is_file($absolutePath)) {
                continue;
            }

            $entryName = $recording->loop_number
                ? sprintf('Loop %d - %s', $recording->loop_number, $recording->filename)
                : $recording->filename;

            $zip->addFile($absolutePath, $entryName);
            $addedFiles++;
        }

        $zip->close();

        if ($addedFiles === 0) {
            @unlink($zipPath);
            throw new \RuntimeException('No recording files were found on disk.');
        }

        $downloadName = Str::slug($session->title ?: 'session') . '.zip';

        return [
            'path' => $zipPath,
            'filename' => $downloadName,
        ];
    }
}
