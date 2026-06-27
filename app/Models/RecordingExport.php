<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordingExport extends Model
{
    use HasUuids;

    public const TYPE_RECORDING = 'recording';

    public const TYPE_SESSION = 'session';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'type',
        'recording_id',
        'recording_session_id',
        'format',
        'status',
        'file_path',
        'download_filename',
        'error_message',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }

    public function recordingSession(): BelongsTo
    {
        return $this->belongsTo(RecordingSession::class);
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_COMPLETED
            && $this->file_path
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
