<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Finding extends Model
{
    const STATUS_UNREVIEWED = 'unreviewed';
    const STATUS_FLAGGED = 'flagged';
    const STATUS_SUPPRESSED = 'suppressed';

    const STATUSES = [
        self::STATUS_UNREVIEWED,
        self::STATUS_FLAGGED,
        self::STATUS_SUPPRESSED,
    ];

    protected $fillable = [
        'project_id',
        'check_id',
        'file_path',
        'start_line',
        'start_col',
        'end_line',
        'end_col',
        'message',
        'status',
        'severity',
        'code_snippet',
        'metadata',
        'scanned_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'start_line' => 'integer',
        'start_col'  => 'integer',
        'end_line'   => 'integer',
        'end_col'    => 'integer',
        'scanned_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function ruleId(): ?int
    {
        return Rule::where('title', $this->check_id)->value('id');
    }

    public function statusIcon(): string
    {
        return match ($this->status) {
            self::STATUS_FLAGGED    => 'bi-flag-fill',
            self::STATUS_SUPPRESSED => 'bi-eye-slash-fill',
            self::STATUS_UNREVIEWED => 'bi-circle',
            default                 => 'bi-circle',
        };
    }

    public function severityColor(): string
    {
        return match (strtoupper($this->severity)) {
            'ERROR'   => 'danger',
            'WARNING' => 'warning',
            'INFO'    => 'info',
            default   => 'secondary',
        };
    }
}
