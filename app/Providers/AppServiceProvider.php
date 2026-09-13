<?php

namespace App\Providers;

use App\Models\EngagementRecord;
use App\Policies\EngagementRecordPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();

        Gate::policy(
            EngagementRecord::class,
            EngagementRecordPolicy::class
        );
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()
                ? 'user:'.$request->user()->getAuthIdentifier()
                : 'ip:'.$request->ip();

            return Limit::perMinute(max(1, config('rate_limits.api_per_minute')))
                ->by($key);
        });

        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(max(1, config('rate_limits.login_per_minute')))
                    ->by('login:'.Str::transliterate($email).'|'.$request->ip()),
                Limit::perMinute(max(1, config('rate_limits.login_ip_per_minute')))
                    ->by('login-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('sensitive', fn (Request $request) => Limit::perMinute(max(1, config('rate_limits.sensitive_per_minute')))
            ->by('sensitive:'.$request->user()?->getAuthIdentifier().'|'.$request->ip())
        );

        RateLimiter::for('pdf-downloads', fn (Request $request) => Limit::perMinute(max(1, config('rate_limits.pdf_per_minute')))
            ->by('pdf:'.$request->user()?->getAuthIdentifier().'|'.$request->ip())
        );
    }
}
