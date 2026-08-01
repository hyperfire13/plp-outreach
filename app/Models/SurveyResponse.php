<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveyResponse extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'community_id',
        'survey_template_id',
        'survey_date',
        'academic_department',
        'conducted_by',
        'status',
        'suggested_outreach_program',
        'remarks',
        'created_by',
        'submitted_by',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'survey_date' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            SurveyTemplate::class,
            'survey_template_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    public function priorityNeeds(): HasMany
    {
        return $this->hasMany(PriorityNeed::class)
            ->orderBy('priority_rank');
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        return $query->when(
            filled($search),
            function (Builder $query) use ($search) {
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery
                        ->where(
                            'academic_department',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'conducted_by',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'community',
                            fn (Builder $communityQuery) =>
                                $communityQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                        )
                        ->orWhereHas(
                            'template',
                            fn (Builder $templateQuery) =>
                                $templateQuery->where(
                                    'title',
                                    'like',
                                    "%{$search}%"
                                )
                        );
                });
            }
        );
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }
}
