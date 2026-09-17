<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectProposal extends Model
{
    use HasFactory, SoftDeletes;

    public const STEPS = ['immediate_head', 'calo_staff', 'calo_head', 'academic_affairs_officer', 'vpaa', 'president'];

    public const STATUSES = ['draft', 'under_review', 'revision_requested', 'rejected', 'approved', 'ntp_issued'];

    protected $guarded = ['id'];

    protected $casts = [
        'proposed_budget' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function priorityNeed()
    {
        return $this->belongsTo(PriorityNeed::class);
    }

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function resources()
    {
        return $this->hasMany(ProjectProposalResource::class);
    }

    public function workplans()
    {
        return $this->hasMany(ProjectProposalWorkplan::class)->orderBy('sort_order');
    }

    public function approvals()
    {
        return $this->hasMany(ProjectProposalApproval::class)->orderBy('acted_at');
    }

    public function documents()
    {
        return $this->hasMany(ProjectProposalDocument::class);
    }

    public function noticeToProceed()
    {
        return $this->hasOne(NoticeToProceed::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'revision_requested'], true);
    }
}
