<?php

namespace App\Http\Controllers;

use App\Models\Recording;
use App\Models\RecordingType;
use App\Models\RecordingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RecordingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Recording::with(['user', 'recordingType'])
            ->orderBy('created_at', 'desc');
        
        // Filter by recording mode if provided
        if ($request->has('mode')) {
            $recordingType = RecordingType::findByName($request->mode);
            if ($recordingType) {
                $query->where('recording_type_id', $recordingType->id);
            }
        }
        
        // Pagination parameters
        $perPage = $request->get('per_page', 20); // Default to 20 recordings per page
        $page = $request->get('page', 1);
        
        // Validate per_page parameter
        if ($perPage > 50) {
            $perPage = 50; // Maximum 50 recordings per page
        }
        
        $recordings = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'recordings' => $recordings->getCollection()->map(function ($recording) {
                return [
                    'id' => $recording->id,
                    'title' => $recording->title,
                    'description' => $recording->description,
                    'duration' => $recording->duration,
                    'formatted_duration' => $recording->formatted_duration,
                    'file_size' => $recording->file_size,
                    'formatted_file_size' => $recording->formatted_file_size,
                    'mime_type' => $recording->mime_type,
                    'created_at' => $recording->created_at?->toIso8601String(),
                    'recording_type' => $recording->recordingType ? [
                        'id' => $recording->recordingType->id,
                        'name' => $recording->recordingType->name,
                        'display_name' => $recording->recordingType->display_name,
                    ] : null,
                    'user' => $recording->user ? [
                        'id' => $recording->user->id,
                        'name' => $recording->user->name,
                    ] : null,
                ];
            }),
            'pagination' => [
                'current_page' => $recordings->currentPage(),
                'per_page' => $recordings->perPage(),
                'total' => $recordings->total(),
                'last_page' => $recordings->lastPage(),
                'has_more_pages' => $recordings->hasMorePages(),
                'from' => $recordings->firstItem(),
                'to' => $recordings->lastItem(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handle both old format (title/description) and new format (FormData from RecordingControls)
        if ($request->hasFile('audio')) {
            // New format from RecordingControls component
            try {
                \Log::info('Recording validation attempt:', [
                    'mode' => $request->mode,
                    'has_audio_file' => $request->hasFile('audio'),
                    'audio_info' => $request->hasFile('audio') ? [
                        'size' => $request->file('audio')->getSize(),
                        'mime_type' => $request->file('audio')->getMimeType(),
                        'extension' => $request->file('audio')->getClientOriginalExtension(),
                        'original_name' => $request->file('audio')->getClientOriginalName(),
                    ] : null,
                    'request_data' => [
                        'duration' => $request->duration,
                        'size' => $request->size,
                        'loops' => $request->loops,
                        'session_id' => $request->session_id,
                    ]
                ]);

                $validation = $request->validate([
                    'audio' => 'required|file|mimetypes:audio/webm,video/webm,audio/wav,audio/mpeg,audio/mp3,audio/ogg,application/octet-stream',
                    'duration' => 'required|string',
                    'size' => 'required|string',
                    'mode' => 'required|string|in:single,looped',
                    'loops' => 'nullable|integer',
                    'session_id' => 'nullable|integer|exists:recording_sessions,id',
                    'loop_number' => 'nullable|integer',
                    'session_title' => 'nullable|string|max:255',
                    'session_description' => 'nullable|string',
                ]);

                \Log::info('Recording validation passed');

                // Additional validation for octet-stream and video/webm files to ensure they're audio files
                $mimeType = $request->file('audio')->getMimeType();
                if ($mimeType === 'application/octet-stream' || $mimeType === 'video/webm') {
                    $allowedExtensions = ['webm', 'wav', 'mp3', 'ogg'];
                    $fileExtension = strtolower($request->file('audio')->getClientOriginalExtension());
                    
                    if (!in_array($fileExtension, $allowedExtensions)) {
                        \Log::error('Extension validation failed for binary/video file', [
                            'mime_type' => $mimeType,
                            'extension' => $fileExtension,
                            'allowed' => $allowedExtensions
                        ]);
                        
                        return response()->json([
                            'message' => 'Validation failed',
                            'errors' => [
                                'audio' => ['The uploaded file must be an audio file with extension: webm, wav, mp3, or ogg.']
                            ]
                        ], 422);
                    }
                    
                    \Log::info('Accepted file with non-standard MIME type', [
                        'mime_type' => $mimeType,
                        'extension' => $fileExtension
                    ]);
                }

            } catch (\Illuminate\Validation\ValidationException $e) {
                // Log detailed validation error information
                \Log::error('Recording validation failed:', [
                    'errors' => $e->errors(),
                    'request_data' => [
                        'has_audio_file' => $request->hasFile('audio'),
                        'audio_file_info' => $request->hasFile('audio') ? [
                            'original_name' => $request->file('audio')->getClientOriginalName(),
                            'size' => $request->file('audio')->getSize(),
                            'mime_type' => $request->file('audio')->getMimeType(),
                            'extension' => $request->file('audio')->getClientOriginalExtension(),
                            'is_valid' => $request->file('audio')->isValid(),
                            'error' => $request->file('audio')->getError(),
                        ] : null,
                        'duration' => $request->duration,
                        'size' => $request->size,
                        'mode' => $request->mode,
                        'loops' => $request->loops,
                        'session_id' => $request->session_id,
                        'loop_number' => $request->loop_number,
                    ]
                ]);
                
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'debug_info' => [
                        'has_audio_file' => $request->hasFile('audio'),
                        'audio_file_info' => $request->hasFile('audio') ? [
                            'original_name' => $request->file('audio')->getClientOriginalName(),
                            'size' => $request->file('audio')->getSize(),
                            'mime_type' => $request->file('audio')->getMimeType(),
                            'extension' => $request->file('audio')->getClientOriginalExtension(),
                            'is_valid' => $request->file('audio')->isValid(),
                            'error' => $request->file('audio')->getError(),
                        ] : null,
                    ]
                ], 422);
            }

            try {
                $audioFile = $request->file('audio');
                $filename = Str::uuid() . '.' . $audioFile->getClientOriginalExtension();
                $filePath = 'recordings/' . $filename;

                // Store the file
                Storage::disk('public')->put($filePath, file_get_contents($audioFile));
                
                // Get file size
                $fileSize = Storage::disk('public')->size($filePath);

                // Parse duration string to seconds for database storage
                $durationParts = explode(':', $request->duration);
                $durationSeconds = ((int)$durationParts[0] * 60) + (int)$durationParts[1];

                // Get or create recording type
                $recordingType = RecordingType::findByName($request->mode);
                if (!$recordingType) {
                    throw new \Exception("Invalid recording mode: {$request->mode}");
                }

                // Handle recording session
                $recordingSession = null;
                $loopNumber = null;

                if ($request->mode === 'looped') {
                    if ($request->session_id) {
                        // Use existing session
                        $recordingSession = RecordingSession::where('id', $request->session_id)
                            ->where('user_id', Auth::id())
                            ->firstOrFail();
                        
                        // Get the next loop number
                        $loopNumber = $recordingSession->recordings()->count() + 1;
                    } else {
                        // Create new session for looped recording
                        $sessionTitle = $request->session_title ?? 'Looped Session';
                        $sessionDescription = $request->session_description ?? 'Looped recording session';

                        $recordingSession = RecordingSession::create([
                            'title' => $sessionTitle,
                            'description' => $sessionDescription,
                            'user_id' => Auth::id(),
                            'recording_type_id' => $recordingType->id,
                            'loop_duration' => $durationSeconds, // Use first recording's duration as loop duration
                        ]);
                        
                        $loopNumber = 1;
                    }
                }

                // Generate title based on mode and session context
                if ($recordingSession) {
                    $title = 'Loop ' . $loopNumber;
                } else {
                    $title = ucfirst($request->mode) . ' Recording';
                }
                
                // Generate description
                $description = 'Recorded in ' . $request->mode . ' mode';
                if ($request->mode === 'looped' && $request->loops) {
                    $description .= ' (loop ' . $loopNumber . ')';
                }

                // Create recording record
                $recording = Recording::create([
                    'title' => $title,
                    'description' => $description,
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'duration' => $durationSeconds,
                    'mime_type' => $audioFile->getMimeType(),
                    'file_size' => $fileSize,
                    'user_id' => Auth::id(),
                    'recording_type_id' => $recordingType->id,
                    'recording_session_id' => $recordingSession ? $recordingSession->id : null,
                    'loop_number' => $loopNumber,
                ]);

                // Update session stats if applicable
                if ($recordingSession) {
                    $recordingSession->updateSessionStats();
                }

                return response()->json([
                    'message' => 'Recording saved successfully',
                    'recording' => [
                        'id' => $recording->id,
                        'title' => $recording->title,
                        'description' => $recording->description,
                        'duration' => $recording->duration,
                        'formatted_duration' => $recording->formatted_duration,
                        'file_size' => $recording->file_size,
                        'formatted_file_size' => $recording->formatted_file_size,
                        'created_at' => $recording->created_at?->toIso8601String(),
                        'file_url' => Storage::url($filePath),
                        'loop_number' => $recording->loop_number,
                        'session_id' => $recording->recording_session_id,
                    ]
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to save recording',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            // Old format with title/description/audio_blob
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'audio_blob' => 'required',
                'duration' => 'nullable|integer',
                'mime_type' => 'nullable|string',
            ]);

            try {
                // Generate unique filename
                $filename = Str::uuid() . '.webm';
                $filePath = 'recordings/' . $filename;

                // Get the audio blob from the request
                $audioBlob = $request->input('audio_blob');
                
                // Decode base64 audio data if it's encoded
                if (strpos($audioBlob, 'data:') === 0) {
                    $audioBlob = explode(',', $audioBlob)[1];
                    $audioBlob = base64_decode($audioBlob);
                }

                // Store the file
                Storage::disk('public')->put($filePath, $audioBlob);

                // Get file size
                $fileSize = Storage::disk('public')->size($filePath);

                // Create recording record
                $recording = Recording::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'filename' => $filename,
                    'file_path' => $filePath,
                    'duration' => $request->duration,
                    'mime_type' => $request->mime_type ?? 'audio/webm',
                    'file_size' => $fileSize,
                    'user_id' => Auth::id(),
                ]);

                return response()->json([
                    'message' => 'Recording saved successfully',
                    'recording' => [
                        'id' => $recording->id,
                        'title' => $recording->title,
                        'description' => $recording->description,
                        'duration' => $recording->duration,
                        'formatted_duration' => $recording->formatted_duration,
                        'file_size' => $recording->file_size,
                        'formatted_file_size' => $recording->formatted_file_size,
                        'created_at' => $recording->created_at?->toIso8601String(),
                        'file_url' => Storage::url($filePath),
                    ]
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to save recording',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Recording $recording)
    {
        $recording->load('user');

        return response()->json([
            'recording' => [
                'id' => $recording->id,
                'title' => $recording->title,
                'description' => $recording->description,
                'duration' => $recording->duration,
                'formatted_duration' => $recording->formatted_duration,
                'file_size' => $recording->file_size,
                'formatted_file_size' => $recording->formatted_file_size,
                'mime_type' => $recording->mime_type,
                'created_at' => $recording->created_at?->toIso8601String(),
                'updated_at' => $recording->updated_at,
                'file_url' => Storage::url($recording->file_path),
                'user' => $recording->user ? [
                    'id' => $recording->user->id,
                    'name' => $recording->user->name,
                ] : null,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recording $recording)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $recording->update($request->only(['title', 'description']));

        return response()->json([
            'message' => 'Recording updated successfully',
            'recording' => [
                'id' => $recording->id,
                'title' => $recording->title,
                'description' => $recording->description,
                'duration' => $recording->duration,
                'formatted_duration' => $recording->formatted_duration,
                'file_size' => $recording->file_size,
                'formatted_file_size' => $recording->formatted_file_size,
                'updated_at' => $recording->updated_at,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recording $recording)
    {
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($recording->file_path)) {
                Storage::disk('public')->delete($recording->file_path);
            }

            // Delete the database record
            $recording->delete();

            return response()->json([
                'message' => 'Recording deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete recording',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the audio file as an attachment.
     */
    public function download(Recording $recording)
    {
        if ($recording->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! Storage::disk('public')->exists($recording->file_path)) {
            return response()->json(['message' => 'Audio file not found'], 404);
        }

        return Storage::disk('public')->download(
            $recording->file_path,
            $recording->filename,
            ['Content-Type' => $recording->mime_type ?? 'application/octet-stream']
        );
    }

    /**
     * Get the audio file for streaming/download
     */
    public function stream(Recording $recording)
    {
        if (!Storage::disk('public')->exists($recording->file_path)) {
            return response()->json(['message' => 'Audio file not found'], 404);
        }

        $file = Storage::disk('public')->get($recording->file_path);
        
        return response($file)
            ->header('Content-Type', $recording->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $recording->filename . '"');
    }
}
