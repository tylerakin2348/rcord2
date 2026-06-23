<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecordingSession extends Model
{
    protected $fillable = [
        'title',
        'description',
        'recording_type_id',
        'user_id',
        'session_duration',
        'total_loops',
        'loop_duration',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    protected $appends = [
        'formatted_session_duration',
    ];

    /**
     * Get the user who owns this recording session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recording type for this session.
     */
    public function recordingType(): BelongsTo
    {
        return $this->belongsTo(RecordingType::class);
    }

    /**
     * Get all recordings in this session.
     */
    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class)->orderBy('loop_number');
    }

    /**
     * Get the formatted session duration.
     */
    public function getFormattedSessionDurationAttribute()
    {
        if (!$this->session_duration) {
            return '0:00';
        }

        $minutes = floor($this->session_duration / 60);
        $seconds = $this->session_duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Calculate and update session statistics from recordings.
     */
    public function updateSessionStats()
    {
        $recordings = $this->recordings;
        
        $this->total_loops = $recordings->count();
        $this->session_duration = $recordings->sum('duration');
        $this->save();
    }

    /**
     * Scope to get sessions by recording type.
     */
    public function scopeByType($query, $type)
    {
        return $query->whereHas('recordingType', function ($q) use ($type) {
            $q->where('name', $type);
        });
    }
}
