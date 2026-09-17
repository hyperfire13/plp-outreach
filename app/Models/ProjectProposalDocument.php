<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectProposalDocument extends Model
{
    protected $guarded = ['id'];

    public function proposal()
    {
        return $this->belongsTo(ProjectProposal::class, 'project_proposal_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
