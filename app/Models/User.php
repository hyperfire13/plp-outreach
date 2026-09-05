<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'birthday',
        'contact_number',
        'email',
        'password',
        'role_id',
        'college_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birthday' => 'date:Y-m-d',
        'password' => 'hashed',
    ];

    protected $appends = [
        'age',
        'full_name',
        'role_name',
        'college_name',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthday
            ? Carbon::parse($this->birthday)->age
            : null;
    }

    public function getFullNameAttribute(): string
    {
        return collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])
            ->filter()
            ->implode(' ');
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->role?->name;
    }

    public function getCollegeNameAttribute(): ?string
    {
        return $this->college?->name;
    }

    public function createdOutreachProjects()
    {
        return $this->hasMany(
            OutreachProject::class,
            'created_by'
        );
    }

    public function createdOutreachPrograms(): HasMany
    {
        return $this->hasMany(OutreachProgram::class, 'created_by');
    }

    public function createdOutreachRecords(): HasMany
    {
        return $this->hasMany(OutreachRecord::class, 'created_by');
    }

    public function coordinatedOutreachProjects()
    {
        return $this->hasMany(
            OutreachProject::class,
            'coordinator_id'
        );
    }

    public function outreachProjectMemberships()
    {
        return $this->belongsToMany(
            OutreachProject::class,
            'outreach_project_members'
        )
            ->withPivot([
                'member_role',
                'status',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function communityPartnerProjects()
    {
        return $this->belongsToMany(
            OutreachProject::class,
            'outreach_project_community_partners'
        )
            ->withPivot([
                'organization_name',
                'contact_person',
                'status',
            ])
            ->withTimestamps();
    }

    public function evaluationAssignments()
    {
        return $this->belongsToMany(
            OutreachProject::class,
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

    public function engagementRecords(): HasMany
    {
        return $this->hasMany(EngagementRecord::class);
    }

    public function encodedEngagementRecords(): HasMany
    {
        return $this->hasMany(EngagementRecord::class, 'encoded_by');
    }

    public function validatedEngagementRecords(): HasMany
    {
        return $this->hasMany(EngagementRecord::class, 'validated_by');
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array(
            $this->role?->name,
            $roles,
            true
        );
    }

    public function isCaloAdministrator(): bool
    {
        return $this->hasRole(
            'calo_administrator',
            'super_admin'
        );
    }

    public function belongsToCollege(?int $collegeId): bool
    {
        return $collegeId !== null
            && $this->college_id === $collegeId;
    }
}
