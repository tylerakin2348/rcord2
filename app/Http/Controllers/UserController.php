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

    /**
     * Get storage usage breakdown by user (admin only).
     */
    public function usersStorage(Request $request)
    {
        // TODO: Add admin role check when authentication roles are implemented
        
        $users = User::with(['recordings'])
            ->get()
            ->map(function ($user) {
                $totalStorage = $user->recordings->sum('file_size') ?? 0;
                $recordingCount = $user->recordings->count();
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'totalStorage' => $totalStorage,
                    'recordingCount' => $recordingCount,
                    'averageFileSize' => $recordingCount > 0 ? round($totalStorage / $recordingCount) : 0,
                    'storagePercentage' => 0, // Will be calculated in summary
                    'accountCreated' => $user->created_at,
                ];
            })
            ->sortByDesc('totalStorage')
            ->values();

        // Calculate total system storage and percentages
        $totalSystemStorage = $users->sum('totalStorage');
        $activeUsers = $users->where('totalStorage', '>', 0)->count();
        $averageStoragePerUser = $activeUsers > 0 ? round($totalSystemStorage / $activeUsers) : 0;

        // Calculate percentages
        $users = $users->map(function ($user) use ($totalSystemStorage) {
            $user['storagePercentage'] = $totalSystemStorage > 0 ? 
                round(($user['totalStorage'] / $totalSystemStorage) * 100, 2) : 0;
            return $user;
        });

        return response()->json([
            'users' => $users,
            'summary' => [
                'totalStorage' => $totalSystemStorage,
                'activeUsers' => $activeUsers,
                'averagePerUser' => $averageStoragePerUser,
            ]
        ]);
    }

    /**
     * Get activity breakdown by user (admin only).
     */
    public function usersActivity(Request $request)
    {
        // TODO: Add admin role check when authentication roles are implemented
        
        $users = User::with(['recordings', 'recordingSessions'])
            ->get()
            ->map(function ($user) {
                $totalRecordings = $user->recordings->count();
                $totalSessions = $user->recordingSessions->count();
                $totalDuration = $user->recordings->sum('duration') ?? 0;
                
                // Recent activity (last 30 days)
                $recentRecordingsCount = $user->recordings()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();
                $recentSessionsCount = $user->recordingSessions()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'totalRecordings' => $totalRecordings,
                    'totalSessions' => $totalSessions,
                    'totalDuration' => $totalDuration,
                    'recentRecordingsCount' => $recentRecordingsCount,
                    'recentSessionsCount' => $recentSessionsCount,
                    'accountCreated' => $user->created_at,
                ];
            })
            ->sortByDesc('totalRecordings')
            ->values();

        // Summary calculations
        $totalRecordings = $users->sum('totalRecordings');
        $totalSessions = $users->sum('totalSessions');
        $activeUsers = $users->where('totalRecordings', '>', 0)->count();
        $averagePerUser = $activeUsers > 0 ? round($totalRecordings / $activeUsers, 1) : 0;

        return response()->json([
            'users' => $users,
            'summary' => [
                'totalRecordings' => $totalRecordings,
                'totalSessions' => $totalSessions,
                'activeUsers' => $activeUsers,
                'averagePerUser' => $averagePerUser,
            ]
        ]);
    }

    /**
     * Get sessions breakdown by user (admin only).
     */
    public function usersSessions(Request $request)
    {
        // TODO: Add admin role check when authentication roles are implemented
        
        $users = User::with(['recordingSessions', 'recordings'])
            ->get()
            ->map(function ($user) {
                $totalSessions = $user->recordingSessions->count();
                $totalRecordings = $user->recordings->count();
                
                // Recent sessions (last 30 days)
                $recentSessions = $user->recordingSessions()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();
                
                // Calculate session statistics
                $totalDuration = $user->recordingSessions->sum('duration') ?? 0;
                $activeSessions = $user->recordingSessions()
                    ->where('ended_at', null)
                    ->count();
                
                $averageRecordingsPerSession = $totalSessions > 0 ? 
                    round($totalRecordings / $totalSessions, 1) : 0;
                
                $lastSession = $user->recordingSessions()
                    ->latest()
                    ->first()?->created_at;
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'totalSessions' => $totalSessions,
                    'activeSessions' => $activeSessions,
                    'totalDuration' => $totalDuration,
                    'recentSessions' => $recentSessions,
                    'averageRecordingsPerSession' => $averageRecordingsPerSession,
                    'lastSession' => $lastSession,
                ];
            })
            ->sortByDesc('totalSessions')
            ->values();

        // Summary calculations
        $totalSessions = $users->sum('totalSessions');
        $activeSessions = $users->sum('activeSessions');
        $totalDuration = $users->sum('totalDuration');
        $usersWithSessions = $users->where('totalSessions', '>', 0)->count();
        $averageDuration = $totalSessions > 0 ? round($totalDuration / $totalSessions) : 0;

        return response()->json([
            'users' => $users,
            'summary' => [
                'totalSessions' => $totalSessions,
                'activeSessions' => $activeSessions,
                'averageDuration' => $averageDuration,
                'usersWithSessions' => $usersWithSessions,
            ]
        ]);
    }
}
