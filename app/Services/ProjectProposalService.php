<?php
namespace App\Services;

use App\Models\NoticeToProceed;
use App\Models\PriorityNeed;
use App\Models\ProjectProposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Notifications\ProjectProposalNotification;

class ProjectProposalService
{
    public function paginate(User $user, array $filters)
    {
        return $this->visibleQuery($user)->with(['applicant:id,first_name,middle_name,last_name','college:id,name','community:id,name','priorityNeed:id,need,status'])
            ->when($filters['status'] ?? null, fn ($q,$v)=>$q->where('status',$v))
            ->when($filters['search'] ?? null, fn ($q,$v)=>$q->where(fn ($n)=>$n->where('title','like',"%{$v}%")->orWhere('proposal_number','like',"%{$v}%")))
            ->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function options(User $user): array
    {
        return ['priority_needs'=>PriorityNeed::query()->with('community:id,name')->where('status','validated')->latest()->get(['id','community_id','need','description']),'statuses'=>ProjectProposal::STATUSES,'steps'=>ProjectProposal::STEPS];
    }

    public function find(ProjectProposal $proposal): ProjectProposal
    {
        return $proposal->load(['applicant:id,first_name,middle_name,last_name,email','college:id,name','community:id,name','priorityNeed:id,community_id,need,status','resources','workplans','approvals.actor:id,first_name,middle_name,last_name,email','documents.uploader:id,first_name,middle_name,last_name','noticeToProceed.issuer:id,first_name,middle_name,last_name']);
    }

    public function store(User $user, array $data): ProjectProposal
    {
        return DB::transaction(function () use ($user,$data) {
            [$attributes,$resources,$workplans]=$this->splitChildren($data);
            $need=PriorityNeed::query()->where('status','validated')->findOrFail($attributes['priority_need_id']);
            $proposal=ProjectProposal::query()->create([...$attributes,'applicant_id'=>$user->id,'college_id'=>$user->college_id,'community_id'=>$need->community_id,'status'=>'draft']);
            $proposal->update(['proposal_number'=>sprintf('CEP-%s-%06d',now()->format('Y'),$proposal->id)]);
            $this->syncChildren($proposal,$resources,$workplans);
            return $this->find($proposal->refresh());
        });
    }

    public function update(ProjectProposal $proposal,array $data): ProjectProposal
    {
        return DB::transaction(function () use ($proposal,$data) {
            [$attributes,$resources,$workplans]=$this->splitChildren($data);
            $need=PriorityNeed::query()->where('status','validated')->findOrFail($attributes['priority_need_id']);
            $proposal->update([...$attributes,'community_id'=>$need->community_id,'status'=>'draft','current_step'=>null]);
            $this->syncChildren($proposal,$resources,$workplans);
            return $this->find($proposal->refresh());
        });
    }

    public function submit(ProjectProposal $proposal): ProjectProposal
    {
        if (!$proposal->isEditable()) throw ValidationException::withMessages(['status'=>['Only draft or revision-requested proposals can be submitted.']]);
        if ($proposal->priorityNeed()->where('status','validated')->doesntExist()) throw ValidationException::withMessages(['priority_need_id'=>['The linked community need is no longer validated.']]);
        $proposal->update(['status'=>'under_review','current_step'=>'immediate_head','submitted_at'=>now()]);
        $this->notifyStepReviewers($proposal, 'A project proposal is awaiting your notation.');
        return $this->find($proposal->refresh());
    }

    public function review(ProjectProposal $proposal,User $actor,string $decision,?string $remarks): ProjectProposal
    {
        return DB::transaction(function () use ($proposal,$actor,$decision,$remarks) {
            $proposal=ProjectProposal::query()->lockForUpdate()->findOrFail($proposal->id);
            $step=$proposal->current_step;
            $proposal->approvals()->create(['step'=>$step,'decision'=>$decision,'acted_by'=>$actor->id,'remarks'=>$remarks,'acted_at'=>now()]);
            if ($decision==='revision') $proposal->update(['status'=>'revision_requested','current_step'=>null]);
            elseif ($decision==='reject') $proposal->update(['status'=>'rejected','current_step'=>null]);
            else {
                $index=array_search($step,ProjectProposal::STEPS,true); $next=ProjectProposal::STEPS[$index+1] ?? null;
                $proposal->update($next ? ['current_step'=>$next] : ['status'=>'approved','current_step'=>null,'approved_at'=>now()]);
            }
            $proposal->applicant->notify(new ProjectProposalNotification($proposal, "Your proposal review status is {$proposal->status}."));
            if ($proposal->current_step) $this->notifyStepReviewers($proposal, 'A project proposal is awaiting your review.');
            return $this->find($proposal->refresh());
        });
    }

    public function issueNtp(ProjectProposal $proposal,User $actor): ProjectProposal
    {
        DB::transaction(function () use ($proposal,$actor) {
            NoticeToProceed::query()->firstOrCreate(['project_proposal_id'=>$proposal->id],['ntp_number'=>sprintf('NTP-%s-%06d',now()->format('Y'),$proposal->id),'issued_by'=>$actor->id,'issued_at'=>now()]);
            $proposal->update(['status'=>'ntp_issued']);
            $proposal->applicant->notify(new ProjectProposalNotification($proposal, 'Your Notice to Proceed is now available.'));
        });
        return $this->find($proposal->refresh());
    }

    public function delete(ProjectProposal $proposal): void { DB::transaction(fn ()=>$proposal->delete()); }
    private function visibleQuery(User $user) { $q=ProjectProposal::query(); return $user->college_id===null ? $q : $q->where(fn ($n)=>$n->where('applicant_id',$user->id)->orWhere('college_id',$user->college_id)); }
    private function splitChildren(array $data): array { $resources=$data['resources']??[]; $workplans=$data['workplans']??[]; unset($data['resources'],$data['workplans']); return [$data,$resources,$workplans]; }
    private function syncChildren(ProjectProposal $proposal,array $resources,array $workplans): void { $proposal->resources()->delete(); $proposal->workplans()->delete(); $proposal->resources()->createMany($resources); $proposal->workplans()->createMany(collect($workplans)->values()->map(fn ($item,$i)=>[...$item,'sort_order'=>$i])->all()); }
    private function notifyStepReviewers(ProjectProposal $proposal,string $message): void
    {
        $role=['immediate_head'=>'college_department_head','calo_staff'=>'calo_staff','calo_head'=>'calo_administrator','academic_affairs_officer'=>'academic_affairs_officer','vpaa'=>'vice_president_academic_affairs','president'=>'university_president'][$proposal->current_step]??null;
        if (!$role) return;
        User::query()->whereHas('role',fn ($q)=>$q->where('name',$role))->when($proposal->current_step==='immediate_head',fn ($q)=>$q->where('college_id',$proposal->college_id))->each(fn (User $reviewer)=>$reviewer->notify(new ProjectProposalNotification($proposal,$message)));
    }
}
