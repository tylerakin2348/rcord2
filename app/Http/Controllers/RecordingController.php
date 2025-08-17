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
        
        $recordings = $query->get();

        return response()->json([
            'recordings' => $recordings->map(function ($recording) {
                return [
                    'id' => $recording->id,
                    'title' => $recording->title,
                    'description' => $recording->description,
                    'duration' => $recording->duration,
                    'formatted_duration' => $recording->formatted_duration,
                    'file_size' => $recording->file_size,
                    'formatted_file_size' => $recording->formatted_file_size,
                    'mime_type' => $recording->mime_type,
                    'created_at' => $recording->created_at,
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
            })
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
            $request->validate([
                'audio' => 'required|file|mimes:webm,wav,mp3,ogg',
                'duration' => 'required|string',
                'size' => 'required|string',
                'mode' => 'required|string|in:single,looped',
                'loops' => 'nullable|integer',
                'session_id' => 'nullable|integer|exists:recording_sessions,id',
                'loop_number' => 'nullable|integer',
                'session_title' => 'nullable|string|max:255',
                'session_description' => 'nullable|string',
            ]);

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
                        $sessionTitle = $request->session_title ?? ('Looped Session - ' . now()->format('M j, Y g:i A'));
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
                    $title = $recordingSession->title . ' - Loop ' . $loopNumber;
                } else {
                    $title = ucfirst($request->mode) . ' Recording - ' . now()->format('M j, Y g:i A');
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
                        'created_at' => $recording->created_at,
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
                        'created_at' => $recording->created_at,
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
                'created_at' => $recording->created_at,
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
