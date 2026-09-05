<?php

namespace App\Services;

use App\Models\Community;
use App\Models\EngagementRecord;
use App\Models\OutreachProgram;
use App\Models\OutreachProject;
use App\Models\PriorityNeed;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DashboardService
{
    public function __construct(
        private readonly EngagementRecordService $engagementRecords,
        private readonly OutreachRecordService $outreachRecords
    ) {}

    public function get(User $user): array
    {
        $projects = $this->visibleProjects($user);
        $engagements = $this->engagementRecords->visibleQuery($user);
        $canViewSurveys = $user->hasRole(...config('role_access.survey_respondents'));
        $canViewAccomplishments = $user->hasRole(...config('role_access.outreach_record_viewers'));

        $cards = [
            ['key' => 'projects', 'label' => 'Outreach Projects', 'value' => (clone $projects)->count(), 'icon' => 'bi-briefcase', 'color' => 'primary', 'route' => 'outreach-projects'],
            ['key' => 'approved_engagements', 'label' => 'Verified Engagements', 'value' => (clone $engagements)->approved()->count(), 'icon' => 'bi-journal-check', 'color' => 'success', 'route' => 'admin-engagement-records'],
            ['key' => 'service_hours', 'label' => 'Verified Service Hours', 'value' => (float) (clone $engagements)->approved()->sum('service_hours'), 'icon' => 'bi-clock-history', 'color' => 'info', 'route' => 'engagement-profile-me'],
        ];

        if ($canViewSurveys) {
            $cards[] = ['key' => 'survey_responses', 'label' => 'Survey Responses', 'value' => SurveyResponse::query()->count(), 'icon' => 'bi-clipboard-data', 'color' => 'warning', 'route' => 'admin-survey-responses'];
            $cards[] = ['key' => 'priority_needs', 'label' => 'Priority Needs', 'value' => PriorityNeed::query()->count(), 'icon' => 'bi-bar-chart', 'color' => 'danger', 'route' => 'admin-priority-needs'];
        }

        if ($canViewAccomplishments) {
            $cards[] = ['key' => 'accomplishments', 'label' => 'Accomplishment Records', 'value' => $this->outreachRecords->visibleQuery($user)->count(), 'icon' => 'bi-clipboard-check', 'color' => 'secondary', 'route' => 'outreach-records'];
        }

        if ($user->hasRole(...config('role_access.community_managers'))) {
            $cards[] = ['key' => 'communities', 'label' => 'Active Communities', 'value' => Community::query()->active()->count(), 'icon' => 'bi-buildings', 'color' => 'success', 'route' => 'admin-communities'];
        }

        if ($user->hasRole(...config('role_access.program_viewers'))) {
            $cards[] = ['key' => 'programs', 'label' => 'Active Programs', 'value' => OutreachProgram::query()->where('is_active', true)->count(), 'icon' => 'bi-collection', 'color' => 'primary', 'route' => 'outreach-programs'];
        }

        return [
            'cards' => $cards,
            'project_statuses' => (clone $projects)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->orderBy('status')
                ->pluck('total', 'status'),
            'pending_engagement_reviews' => $user->hasRole(...config('role_access.engagement_reviewers'))
                ? (clone $engagements)->where('status', EngagementRecord::STATUS_SUBMITTED)->count()
                : 0,
            'recent_projects' => (clone $projects)
                ->with(['program:id,name', 'college:id,name'])
                ->latest('updated_at')
                ->limit(5)
                ->get(['id', 'outreach_program_id', 'college_id', 'title', 'status', 'updated_at']),
            'recent_engagements' => (clone $engagements)
                ->with('user:id,first_name,middle_name,last_name')
                ->latest('updated_at')
                ->limit(5)
                ->get(['id', 'user_id', 'title', 'status', 'activity_date', 'updated_at']),
        ];
    }

    private function visibleProjects(User $user): Builder
    {
        $query = OutreachProject::query();

        if ($user->hasRole('super_admin', 'calo_administrator', 'monitoring_evaluation_team')) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('coordinator_id', $user->id)
                ->orWhereHas('members', fn (Builder $related) => $related->whereKey($user->id))
                ->orWhereHas('communityPartners', fn (Builder $related) => $related->whereKey($user->id))
                ->orWhereHas('evaluators', fn (Builder $related) => $related->whereKey($user->id));

            if ($user->college_id !== null && $user->hasRole('college_admin', 'faculty_extension_coordinator', 'college_department_head')) {
                $query->orWhere('college_id', $user->college_id);
            }
        });
    }
}
