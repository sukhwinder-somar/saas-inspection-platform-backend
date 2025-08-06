<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'checklist_template_id',
        'inspection_number',
        'status',
        'started_at',
        'completed_at',
        'location',
        'weather_conditions',
        'notes',
        'inspector_signature',
        'supervisor_signature',
        'critical_issues_found',
        'follow_up_required',
        'follow_up_notes',
        'inspection_data',
        'photo_urls',
        'gps_coordinates',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'inspection_data' => 'array',
        'photo_urls' => 'array',
        'gps_coordinates' => 'array',
        'critical_issues_found' => 'boolean',
        'follow_up_required' => 'boolean',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_OVERDUE = 'overdue';

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(InspectionResponse::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE);
    }

    public function scopeCritical($query)
    {
        return $query->where('critical_issues_found', true);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE;
    }

    public function hasCriticalIssues(): bool
    {
        return $this->critical_issues_found;
    }

    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->completed_at->diffInMinutes($this->started_at);
        }
        return null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($inspection) {
            if (!$inspection->inspection_number) {
                $inspection->inspection_number = 'INS-' . strtoupper(uniqid());
            }
        });
    }
}
