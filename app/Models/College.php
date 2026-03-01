<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class College extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'location',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A college has many users.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * A college has many outreach records.
     */
    public function outreachRecords()
    {
        return $this->hasMany(OutreachRecord::class);
    }

    /**
     * A college may participate in many outreach programs (via records).
     */
    public function outreachPrograms()
    {
        return $this->hasManyThrough(
            OutreachProgram::class,
            OutreachRecord::class,
            'college_id',
            'id',
            'id',
            'outreach_program_id'
        );
    }
}