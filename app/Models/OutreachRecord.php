<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutreachRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'outreach_program_id',
        'college_id',
        'budget_used',
        'volunteers_count',
        'impact_score',
        'success_rate',
        'satisfaction_rating',
        'execution_date',
        'created_by',
    ];

    protected $casts = [
        'budget_used' => 'float',
        'volunteers_count' => 'integer',
        'impact_score' => 'float',
        'success_rate' => 'float',
        'satisfaction_rating' => 'float',
        'execution_date' => 'date',
    ];

    /* ==============================
       Relationships
    ============================== */

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function outreachProgram()
    {
        return $this->belongsTo(OutreachProgram::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
