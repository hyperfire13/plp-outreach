<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class NoticeToProceed extends Model { protected $guarded = ['id']; protected $casts = ['issued_at'=>'datetime']; public function proposal() { return $this->belongsTo(ProjectProposal::class, 'project_proposal_id'); } public function issuer() { return $this->belongsTo(User::class, 'issued_by'); } }
