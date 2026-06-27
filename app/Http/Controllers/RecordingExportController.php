<?php

namespace App\Http\Controllers;

use App\Models\Recording;
use App\Models\RecordingExport;
use App\Models\RecordingSession;
use App\Services\RecordingExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RecordingExportController extends Controller
{
    public function __construct(
        private RecordingExportService $exportService,
    ) {}

    public function storeRecording(Request $request, Recording $recording): JsonResponse
    {
        $validated = $request->validate([
            'format' => 'required|string|in:mp3,wav',
        ]);

        $export = $this->exportService->requestRecordingExport(
            $recording,
            Auth::user(),
            $validated['format'],
        );

        return response()->json([
            'export' => $this->transformExport($export),
        ], 202);
    }

    public function storeSession(Request $request, RecordingSession $session): JsonResponse
    {
        $validated = $request->validate([
            'format' => 'required|string|in:mp3,wav',
        ]);

        $export = $this->exportService->requestSessionExport(
            $session,
            Auth::user(),
            $validated['format'],
        );

        return response()->json([
            'export' => $this->transformExport($export),
        ], 202);
    }

    public function show(RecordingExport $export): JsonResponse
    {
        $this->authorizeExport($export);

        return response()->json([
            'export' => $this->transformExport($export->fresh()),
        ]);
    }

    public function download(RecordingExport $export): JsonResponse|BinaryFileResponse
    {
        $this->authorizeExport($export);

        if ($export->status === RecordingExport::STATUS_FAILED) {
            return response()->json([
                'message' => $export->error_message ?: 'Export failed.',
            ], 422);
        }

        if (! $export->isReady()) {
            return response()->json([
                'message' => 'Export is not ready yet.',
                'status' => $export->status,
            ], 409);
        }

        if (! Storage::disk('local')->exists($export->file_path)) {
            return response()->json([
                'message' => 'Export file was not found.',
            ], 404);
        }

        return Storage::disk('local')->download(
            $export->file_path,
            $export->download_filename,
        );
    }

    private function authorizeExport(RecordingExport $export): void
    {
        if ($export->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
    }

    private function transformExport(RecordingExport $export): array
    {
        return [
            'id' => $export->id,
            'type' => $export->type,
            'format' => $export->format,
            'status' => $export->status,
            'download_filename' => $export->download_filename,
            'error_message' => $export->error_message,
            'is_ready' => $export->isReady(),
            'expires_at' => $export->expires_at?->toIso8601String(),
            'created_at' => $export->created_at?->toIso8601String(),
        ];
    }
}
