<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectProposalApproval extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['acted_at' => 'datetime'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
