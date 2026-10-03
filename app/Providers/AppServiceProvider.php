<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('admin', fn(User $user) => $user->isAdmin() && $user->isActive());
        Gate::define('pengelola', fn(User $user) => $user->isPengelola() && $user->isActive());
        Gate::define('penyewa', fn(User $user) => $user->isPenyewa() && $user->isActive());

        Gate::define('manage-venue', function (User $user, Venue $venue) {
            return ($user->isAdmin() || ($user->isPengelola() && $venue->owner_id === $user->id)) && $user->isActive();
        });
    }
}
