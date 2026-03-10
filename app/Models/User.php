<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'college_id',
        'first_name',
        'middle_name',
        'last_name',
        'birthday',
        'contact_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $appends = [
        'age',
        'full_name'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function getAgeAttribute()
    {
        if (!$this->birthday) {
            return null;
        }
        return Carbon::parse($this->birthday)->age;
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}