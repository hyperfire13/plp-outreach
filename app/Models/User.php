<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
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
}
