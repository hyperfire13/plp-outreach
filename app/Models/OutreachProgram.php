<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutreachProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'description',
        'typical_budget',
        'typical_duration_days',
        'is_active',
    ];

    protected $casts = [
        'typical_budget' => 'decimal:2',
        'typical_duration_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function projects()
    {
        return $this->hasMany(OutreachProject::class);
    }
}
