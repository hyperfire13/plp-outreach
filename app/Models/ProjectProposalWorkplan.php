<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProjectProposalWorkplan extends Model { protected $guarded = ['id']; protected $casts = ['start_date'=>'date:Y-m-d','end_date'=>'date:Y-m-d']; public function proposal() { return $this->belongsTo(ProjectProposal::class, 'project_proposal_id'); } }
