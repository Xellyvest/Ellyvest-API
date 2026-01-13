<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->name('api')
                ->group(base_path('routes/api/generic.php'));

            Route::middleware('api')
                ->prefix('api/user')
                ->name('api.user')
                ->group(base_path('routes/api/user.php'));

            Route::middleware('api')
                ->prefix('api/admin')
                ->name('api.admin')
                ->group(base_path('routes/api/admin.php'));

            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'unblocked' => \App\Http\Middleware\EnsureAccountIsUnblocked::class,
            'auth.two_fa' => \App\Http\Middleware\AuthenticateTwoFa::class,
            'password.secure' => \App\Http\Middleware\RequireSecurePassword::class,

            'guest:admin' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'auth:admin' => RoleMiddleware::class,
            'active_admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {

        $schedule->command('assets:update')
             ->everyFiveMinutes()
             ->withoutOverlapping()
             ->onOneServer()
             ->runInBackground();

        $schedule->command('db:backup-email')
             ->dailyAt('00:00')
             ->withoutOverlapping()
             ->onOneServer()
             ->runInBackground();

        $schedule->command('dividends:process')
             ->dailyAt('00:00')
             ->withoutOverlapping()
             ->timezone('UTC');
             
        $schedule->command('record:profit-loss-history')
            ->withoutOverlapping()
            ->runInBackground()
            ->hourly();

        // $schedule->command('auto-investments:process-expired')
        //     ->everyMinute()
        //     ->withoutOverlapping()
        //     ->runInBackground();

    })->create();
