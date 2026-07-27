<?php

namespace App\Providers;

use App\Models\UserActivityLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Password::defaults(function () {
            return Password::min(10)
                ->mixedCase()
                ->numbers()
                ->symbols();
        });

        Event::listen(Login::class, function (Login $event) {
            UserActivityLog::create([
                'user_id' => $event->user->id,
                'user_name' => $event->user->name,
                'action' => UserActivityLog::LOGIN,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (!$event->user) {
                return;
            }

            UserActivityLog::create([
                'user_id' => $event->user->id,
                'user_name' => $event->user->name,
                'action' => UserActivityLog::LOGOUT,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        });

        Event::listen(Failed::class, function (Failed $event) {
            UserActivityLog::create([
                'user_id' => $event->user?->id,
                'user_name' => $event->user?->name ?? $event->credentials['email'] ?? 'Unknown',
                'action' => UserActivityLog::FAILED_LOGIN,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        });
    }
}
