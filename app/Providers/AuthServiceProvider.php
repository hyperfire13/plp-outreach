<?php

namespace App\Providers;

use App\Models\OutreachProgram;
use App\Models\OutreachRecord;
use App\Models\User;
use App\Models\ProjectProposal;
use App\Policies\ProjectProposalPolicy;
use App\Policies\OutreachProgramPolicy;
use App\Policies\OutreachRecordPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        OutreachProgram::class => OutreachProgramPolicy::class,
        OutreachRecord::class => OutreachRecordPolicy::class,
        ProjectProposal::class => ProjectProposalPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
