<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\IteneraryPolicy;
use App\Policies\UserPolicy;
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
        Gate::define('view-user', [UserPolicy::class, 'view']);
        Gate::define('create-user', [UserPolicy::class, 'create']);
        Gate::define('update-user', [UserPolicy::class, 'update']);
        Gate::define('delete-user', [UserPolicy::class, 'delete']);
        Gate::define('login', [UserPolicy::class, 'login']);


        Gate::define('viewAny-itenerary', [IteneraryPolicy::class, 'viewAny']);
        Gate::define('view-itenerary', [IteneraryPolicy::class, 'view']);
        Gate::define('create-itenerary', [IteneraryPolicy::class, 'create']);
        Gate::define('update-itenerary', [IteneraryPolicy::class, 'update']);
        Gate::define('delete-itenerary', [IteneraryPolicy::class, 'delete']);
        Gate::define('restore-itenerary', [IteneraryPolicy::class, 'restore']);
        Gate::define('copy-itenerary', [IteneraryPolicy::class, 'copy']);

        Gate::define('create-destination', [\App\Policies\DestinationPolicy::class, 'create']);
        Gate::define('update-destination', [\App\Policies\DestinationPolicy::class, 'update']);
        Gate::define('delete-destination', [\App\Policies\DestinationPolicy::class, 'delete']);

    }

    protected $policies= [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Itenerary::class => \App\Policies\IteneraryPolicy::class,
    ];
}
