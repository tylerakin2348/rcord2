<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the recordings for the user.
     */
    public function recordings()
    {
        return $this->hasMany(Recording::class);
    }

    /**
     * Get the recording sessions for the user.
     */
    public function recordingSessions()
    {
        return $this->hasMany(RecordingSession::class);
    }

    /**
     * Get user statistics.
     */
    public function getStats()
    {
        $recordings = $this->recordings();
        $sessions = $this->recordingSessions();
        
        $totalRecordings = $recordings->count();
        $totalSessions = $sessions->count();
        
        // Calculate total duration from recordings
        $totalDuration = $recordings->sum('duration') ?? 0;
        
        // Calculate total storage used (file_size is in bytes)
        $totalStorageBytes = $recordings->sum('file_size') ?? 0;
        
        // Recent activity (last 30 days)
        $recentRecordings = $recordings->where('created_at', '>=', now()->subDays(30))->count();
        $recentSessions = $sessions->where('created_at', '>=', now()->subDays(30))->count();
        
        return [
            'totalRecordings' => $totalRecordings,
            'totalSessions' => $totalSessions,
            'totalDuration' => $totalDuration,
            'totalStorageBytes' => $totalStorageBytes,
            'averageRecordingDuration' => $totalRecordings > 0 ? round($totalDuration / $totalRecordings) : 0,
            'averageFileSize' => $totalRecordings > 0 ? round($totalStorageBytes / $totalRecordings) : 0,
            'recentRecordingsCount' => $recentRecordings,
            'recentSessionsCount' => $recentSessions,
            'accountCreated' => $this->created_at->format('Y-m-d'),
            'lastActive' => $this->updated_at->format('Y-m-d'),
        ];
    }
}
