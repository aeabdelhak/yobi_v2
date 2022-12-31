<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
          'App\Models\store' => 'App\Policies\StorePolicy',
          'App\Models\order' => 'App\Policies\OrderPolicy',
          'App\Models\landingPage' => 'App\Policies\LandingPagePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
         $this->registerPolicies();
    }
}