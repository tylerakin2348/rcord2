<?php

namespace App\Http\Controllers;

use App\Models\RecordingSession;
use App\Models\RecordingType;
use App\Services\SessionZipExporter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RecordingSessionController extends Controller
{
    /**
     * Display a listing of recording sessions for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->recordingSessions()->with(['recordingType', 'recordings' => function ($query) {
            $query->orderBy('loop_number')->orderBy('created_at');
        }]);

        // Filter by recording type if provided
        if ($request->has('type')) {
            $recordingType = RecordingType::where('name', $request->type)->first();
            if ($recordingType) {
                $query->where('recording_type_id', $recordingType->id);
            }
        }

        // Pagination parameters
        $perPage = $request->get('per_page', 20); // Default to 20 sessions per page
        $page = $request->get('page', 1);
        
        // Validate per_page parameter
        if ($perPage > 50) {
            $perPage = 50; // Maximum 50 sessions per page
        }

        $sessions = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'sessions' => $sessions->getCollection()->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'description' => $session->description,
                    'recording_type' => $session->recordingType->name,
                    'session_duration' => $session->session_duration,
                    'formatted_session_duration' => $session->formatted_session_duration,
                    'total_loops' => $session->total_loops,
                    'loop_duration' => $session->loop_duration,
                    'settings' => $session->settings,
                    'recordings_count' => $session->recordings->count(),
                    'recordings' => $session->recordings->map(function ($recording) {
                        return [
                            'id' => $recording->id,
                            'filename' => $recording->filename,
                            'file_path' => $recording->file_path,
                            'title' => $recording->title,
                            'duration' => $recording->duration,
                            'formatted_duration' => $recording->formatted_duration,
                            'file_size' => $recording->file_size,
                            'formatted_file_size' => $recording->formatted_file_size,
                            'loop_number' => $recording->loop_number,
                            'created_at' => $recording->created_at?->toIso8601String(),
                        ];
                    }),
                    'created_at' => $session->created_at?->toIso8601String(),
                    'updated_at' => $session->updated_at?->toIso8601String(),
                ];
            }),
            'pagination' => [
                'current_page' => $sessions->currentPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
                'last_page' => $sessions->lastPage(),
                'has_more_pages' => $sessions->hasMorePages(),
                'from' => $sessions->firstItem(),
                'to' => $sessions->lastItem(),
            ]
        ]);
    }

    /**
     * Store a new recording session
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recording_type' => 'required|string|in:single,looped',
            'loop_duration' => 'nullable|integer|min:1',
            'settings' => 'nullable|array',
        ]);

        $recordingType = RecordingType::where('name', $validated['recording_type'])->firstOrFail();

        $session = RecordingSession::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
            'recording_type_id' => $recordingType->id,
            'loop_duration' => $validated['loop_duration'],
            'settings' => $validated['settings'] ?? null,
        ]);

        $session->load(['recordingType', 'recordings']);

        return response()->json([
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'description' => $session->description,
                'recording_type' => $session->recordingType->name,
                'session_duration' => $session->session_duration,
                'formatted_session_duration' => $session->formatted_session_duration,
                'total_loops' => $session->total_loops,
                'loop_duration' => $session->loop_duration,
                'settings' => $session->settings,
                'recordings_count' => $session->recordings->count(),
                'created_at' => $session->created_at?->toIso8601String(),
                'updated_at' => $session->updated_at?->toIso8601String(),
            ]
        ], 201);
    }

    /**
     * Display the specified recording session
     */
    public function show(RecordingSession $session): JsonResponse
    {
        // Ensure user can only access their own sessions
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session->load(['recordingType', 'recordings' => function ($query) {
            $query->orderBy('loop_number')->orderBy('created_at');
        }]);

        return response()->json([
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'description' => $session->description,
                'recording_type' => $session->recordingType->name,
                'session_duration' => $session->session_duration,
                'formatted_session_duration' => $session->formatted_session_duration,
                'total_loops' => $session->total_loops,
                'loop_duration' => $session->loop_duration,
                'settings' => $session->settings,
                'recordings' => $session->recordings->map(function ($recording) {
                    return [
                        'id' => $recording->id,
                        'filename' => $recording->filename,
                        'file_path' => $recording->file_path,
                        'duration' => $recording->duration,
                        'formatted_duration' => $recording->formatted_duration,
                        'file_size' => $recording->file_size,
                        'formatted_file_size' => $recording->formatted_file_size,
                        'loop_number' => $recording->loop_number,
                        'created_at' => $recording->created_at?->toIso8601String(),
                    ];
                }),
                'created_at' => $session->created_at?->toIso8601String(),
                'updated_at' => $session->updated_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Update the specified recording session
     */
    public function update(Request $request, RecordingSession $session): JsonResponse
    {
        // Ensure user can only update their own sessions
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $session->update($validated);
        $session->load(['recordingType', 'recordings']);

        return response()->json([
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'description' => $session->description,
                'recording_type' => $session->recordingType->name,
                'session_duration' => $session->session_duration,
                'formatted_session_duration' => $session->formatted_session_duration,
                'total_loops' => $session->total_loops,
                'loop_duration' => $session->loop_duration,
                'settings' => $session->settings,
                'recordings_count' => $session->recordings->count(),
                'created_at' => $session->created_at?->toIso8601String(),
                'updated_at' => $session->updated_at?->toIso8601String(),
            ]
        ]);
    }

    /**
     * Remove the specified recording session and all associated recordings
     */
    public function destroy(RecordingSession $session): JsonResponse
    {
        // Ensure user can only delete their own sessions
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete associated recordings first (this will also delete the files)
        foreach ($session->recordings as $recording) {
            // Delete the file from storage
            if (file_exists(storage_path('app/' . $recording->file_path))) {
                unlink(storage_path('app/' . $recording->file_path));
            }
            $recording->delete();
        }

        $session->delete();

        return response()->json(['message' => 'Recording session deleted successfully']);
    }

    /**
     * Download all recordings in a session as a zip archive.
     */
    public function download(RecordingSession $session, SessionZipExporter $exporter): JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $archive = $exporter->export($session);

            return response()
                ->download($archive['path'], $archive['filename'])
                ->deleteFileAfterSend(true);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }
}
