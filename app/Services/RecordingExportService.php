<?php

namespace App\Services;

use App\Jobs\ProcessRecordingExport;
use App\Jobs\ProcessSessionExport;
use App\Models\Recording;
use App\Models\RecordingExport;
use App\Models\RecordingSession;
use App\Models\User;

class RecordingExportService
{
    public function __construct(
        private AudioConverter $converter,
    ) {}

    public function requestRecordingExport(Recording $recording, User $user, string $format): RecordingExport
    {
        $this->converter->assertFormat($format);
        $this->assertRecordingOwnership($recording, $user);

        $existing = $this->findActiveExport(
            user: $user,
            format: $format,
            recordingId: $recording->id,
        );

        if ($existing) {
            return $existing;
        }

        $export = RecordingExport::create([
            'user_id' => $user->id,
            'type' => RecordingExport::TYPE_RECORDING,
            'recording_id' => $recording->id,
            'format' => $format,
            'status' => RecordingExport::STATUS_PENDING,
            'download_filename' => $this->converter->makeDownloadFilename(
                $recording->title ?: 'recording',
                $format,
            ),
        ]);

        ProcessRecordingExport::dispatch($export);

        return $export;
    }

    public function requestSessionExport(RecordingSession $session, User $user, string $format): RecordingExport
    {
        $this->converter->assertFormat($format);
        $this->assertSessionOwnership($session, $user);

        $existing = $this->findActiveExport(
            user: $user,
            format: $format,
            sessionId: $session->id,
        );

        if ($existing) {
            return $existing;
        }

        $export = RecordingExport::create([
            'user_id' => $user->id,
            'type' => RecordingExport::TYPE_SESSION,
            'recording_session_id' => $session->id,
            'format' => $format,
            'status' => RecordingExport::STATUS_PENDING,
            'download_filename' => $this->converter->makeDownloadFilename(
                $session->title ?: 'session',
                $format,
                isZip: true,
            ),
        ]);

        ProcessSessionExport::dispatch($export);

        return $export;
    }

    private function findActiveExport(
        User $user,
        string $format,
        ?int $recordingId = null,
        ?int $sessionId = null,
    ): ?RecordingExport {
        $query = RecordingExport::query()
            ->where('user_id', $user->id)
            ->where('format', $format)
            ->whereIn('status', [
                RecordingExport::STATUS_PENDING,
                RecordingExport::STATUS_PROCESSING,
            ]);

        if ($recordingId) {
            $query->where('recording_id', $recordingId);
        }

        if ($sessionId) {
            $query->where('recording_session_id', $sessionId);
        }

        return $query->latest()->first();
    }

    private function assertRecordingOwnership(Recording $recording, User $user): void
    {
        if ($recording->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
    }

    private function assertSessionOwnership(RecordingSession $session, User $user): void
    {
        if ($session->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
    }

    public static function exportStoragePath(RecordingExport $export, ?string $extension = null): string
    {
        $extension ??= $export->type === RecordingExport::TYPE_SESSION
            ? 'zip'
            : $export->format;

        return 'exports/' . $export->id . '.' . $extension;
    }
}
