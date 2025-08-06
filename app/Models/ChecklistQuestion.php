<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'question',
        'type',
        'options',
        'required',
        'conditional_logic',
        'notification_message',
        'order',
        'section',
    ];

    protected $casts = [
        'options' => 'array',
        'conditional_logic' => 'array',
        'required' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(ChecklistTemplate::class, 'template_id');
    }

    public function responses()
    {
        return $this->hasMany(InspectionResponse::class, 'question_id');
    }
}
