<?php

namespace App\Providers;

use App\Models\User;
use App\Models\OutreachProgram;
use App\Policies\OutreachProgramPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        OutreachProgram::class => OutreachProgramPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
