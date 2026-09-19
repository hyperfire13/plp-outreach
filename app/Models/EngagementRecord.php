<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EngagementRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_OUTREACH = 'outreach';

    public const TYPE_EXTENSION = 'extension';

    public const TYPE_VOLUNTEERISM = 'volunteerism';

    public const TYPE_SERVICE_LEARNING = 'service_learning';

    public const TYPE_TRAINING = 'training';

    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_OUTREACH,
        self::TYPE_EXTENSION,
        self::TYPE_VOLUNTEERISM,
        self::TYPE_SERVICE_LEARNING,
        self::TYPE_TRAINING,
        self::TYPE_OTHER,
    ];

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_PROJECT_ROSTER = 'project_roster';

    public const SOURCE_TYPES = [
        self::SOURCE_MANUAL,
        self::SOURCE_PROJECT_ROSTER,
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    public const SDGS = [
        'sdg_1', 'sdg_2', 'sdg_3', 'sdg_4', 'sdg_5', 'sdg_6',
        'sdg_7', 'sdg_8', 'sdg_9', 'sdg_10', 'sdg_11', 'sdg_12',
        'sdg_13', 'sdg_14', 'sdg_15', 'sdg_16', 'sdg_17',
    ];

    protected $fillable = [
        'user_id',
        'engagement_group_uuid',
        'outreach_project_id',
        'community_id',
        'title',
        'engagement_type',
        'participation_role',
        'activity_date',
        'service_hours',
        'sdg',
        'description',
        'source_type',
        'status',
        'encoded_by',
        'validated_by',
        'submitted_at',
        'validated_at',
        'validation_remarks',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date:Y-m-d',
            'service_hours' => 'decimal:2',
            'submitted_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EngagementRecord $record): void {
            $record->engagement_group_uuid ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            OutreachProject::class,
            'outreach_project_id'
        );
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(
            filled($search),
            function (Builder $query) use ($search) {
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('participation_role', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas(
                            'user',
                            fn (Builder $userQuery) => $userQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                        );
                });
            }
        );
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REJECTED,
        ], true);
    }
}
