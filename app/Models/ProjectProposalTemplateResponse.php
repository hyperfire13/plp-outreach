<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProposalTemplateResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_application_template_id',
        'response_data',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'response_data' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ProjectProposal::class, 'project_proposal_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProjectApplicationTemplate::class, 'project_application_template_id');
    }
}
