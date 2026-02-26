<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutreachProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'description',
        'typical_budget',
        'typical_duration_days',
        'is_active'
    ];

    protected $casts = [
        'typical_budget' => 'float',
        'typical_duration_days' => 'integer',
        'is_active' => 'boolean'
    ];
}