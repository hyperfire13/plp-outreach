<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriorityNeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_response_id',
        'community_id',
        'need',
        'priority_rank',
        'description',
        'status',
        'validated_by',
        'validated_at',
        'validation_remarks',
    ];

    protected function casts(): array
    {
        return [
            'priority_rank' => 'integer',
            'validated_at' => 'datetime',
        ];
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(
            SurveyResponse::class,
            'survey_response_id'
        );
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
