<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'region',
        'population',
        'poverty_rate',
        'unemployment_rate',
        'literacy_rate',
        'avg_income',
        'urban_rural',
        'disaster_risk_level',
        'health_risk_index',
        'infrastructure_score'
    ];

    protected $casts = [
        'population' => 'integer',
        'poverty_rate' => 'float',
        'unemployment_rate' => 'float',
        'literacy_rate' => 'float',
        'avg_income' => 'float',
        'health_risk_index' => 'float',
        'infrastructure_score' => 'float',
    ];
}