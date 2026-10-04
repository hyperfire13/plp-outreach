<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectApplicationTemplate extends Model
{
    use HasFactory, SoftDeletes;

    public const PHASES = ['application', 'implementation', 'monitoring'];

    public const STATUSES = ['draft', 'published', 'archived'];

    protected $fillable = [
        'code',
        'title',
        'description',
        'version',
        'phase',
        'status',
        'schema',
        'is_required',
        'sort_order',
        'created_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'schema' => 'array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ProjectProposalTemplateResponse::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(filled($search), fn (Builder $query) => $query
            ->where(fn (Builder $nested) => $nested
                ->where('title', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")));
    }
}
