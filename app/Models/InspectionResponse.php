<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'question_id',
        'question_text',
        'question_type',
        'response_value',
        'response_text',
        'response_file_url',
        'is_critical',
        'notes',
        'section_name',
        'order',
    ];

    protected $casts = [
        'response_value' => 'array',
        'is_critical' => 'boolean',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeBySection($query, $section)
    {
        return $query->where('section_name', $section);
    }
}
