<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProjectProposalResource extends Model { protected $guarded = ['id']; public function proposal() { return $this->belongsTo(ProjectProposal::class, 'project_proposal_id'); } }
