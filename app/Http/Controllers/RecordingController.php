<?php

namespace App\Http\Controllers;

use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RecordingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recordings = Recording::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

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
