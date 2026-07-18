<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutreachProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outreach_program_id',
        'college_id',
        'created_by',
        'coordinator_id',
        'title',
        'description',
        'objectives',
        'location',
        'start_date',
        'end_date',
        'proposed_budget',
        'expected_beneficiaries',
        'status',
        'submitted_at',
        'approved_at',
        'completed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'proposed_budget' => 'decimal:2',
        'expected_beneficiaries' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(
            OutreachProgram::class,
            'outreach_program_id'
        );
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function coordinator()
    {
        return $this->belongsTo(
            User::class,
            'coordinator_id'
        );
    }

    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'outreach_project_members'
        )
            ->withPivot([
                'member_role',
                'status',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function communityPartners()
    {
        return $this->belongsToMany(
            User::class,
            'outreach_project_community_partners'
        )
            ->withPivot([
                'organization_name',
                'contact_person',
                'status',
            ])
            ->withTimestamps();
    }

    public function evaluators()
    {
        return $this->belongsToMany(
            User::class,
            'outreach_project_evaluators'
        )
            ->withPivot([
                'evaluation_type',
                'status',
                'assigned_at',
                'completed_at',
            ])
            ->withTimestamps();
    }
}
