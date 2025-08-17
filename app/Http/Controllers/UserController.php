<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(15);
        
        return response()->json($users);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json(['message' => 'Create form endpoint']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'user' => $user,
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        
        return response()->json(['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        
        return response()->json(['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        return response()->json([
            'user' => $user,
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get statistics for the current authenticated user.
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $stats = $user->getStats();

        return response()->json($stats);
    }

    /**
     * Get system-wide statistics (admin only).
     */
    public function systemStats(Request $request)
    {
        // TODO: Add admin role check when authentication roles are implemented
        // For now, any authenticated user can access system stats
        
        $totalUsers = User::count();
        $totalRecordings = \App\Models\Recording::count();
        $totalSessions = \App\Models\RecordingSession::count();
        
        // Calculate total duration across all recordings
        $totalDuration = \App\Models\Recording::sum('duration') ?? 0;
        
        // Calculate total storage used across all recordings
        $totalStorageBytes = \App\Models\Recording::sum('file_size') ?? 0;
        
        // Recent activity (last 30 days)
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $recentRecordings = \App\Models\Recording::where('created_at', '>=', now()->subDays(30))->count();
        $recentSessions = \App\Models\RecordingSession::where('created_at', '>=', now()->subDays(30))->count();
        
        // Average statistics
        $averageRecordingDuration = $totalRecordings > 0 ? round($totalDuration / $totalRecordings) : 0;
        $averageFileSize = $totalRecordings > 0 ? round($totalStorageBytes / $totalRecordings) : 0;
        $averageRecordingsPerUser = $totalUsers > 0 ? round($totalRecordings / $totalUsers, 1) : 0;
        
        return response()->json([
            'totalUsers' => $totalUsers,
            'totalRecordings' => $totalRecordings,
            'totalSessions' => $totalSessions,
            'totalDuration' => $totalDuration,
            'totalStorageBytes' => $totalStorageBytes,
            'recentUsers' => $recentUsers,
            'recentRecordings' => $recentRecordings,
            'recentSessions' => $recentSessions,
            'averageRecordingDuration' => $averageRecordingDuration,
            'averageFileSize' => $averageFileSize,
            'averageRecordingsPerUser' => $averageRecordingsPerUser,
            'systemStartDate' => User::orderBy('created_at')->first()?->created_at->format('Y-m-d'),
        ]);
    }
}
