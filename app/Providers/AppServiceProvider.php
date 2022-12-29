<?php

namespace App\Providers;

use App\Enums\permissions;
use App\Enums\userRoles;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Concerns\ToArray;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::define('admin', function(User $user) {
            return $user->isAdmin();
        });    
        Gate::define('manage_users', function(User $user) {
            return $user->isAdmin();
        });    
        Gate::define('manage_landing_pages', function(User $user) {
            $permissions=(array) $user->permissions();
            return $user->isAdmin() ?? in_array(permissions::$landing,$permissions );
        });    
        Gate::define('manage_orders', function(User $user) {
            $permissions=(array) $user->permissions();
            return $user->isAdmin() ?? in_array(permissions::$orders, $permissions);
        });    
        Gate::define('manage_themes', function(User $user) {
            $permissions=(array) $user->permissions();
            return $user->isAdmin() ?? in_array(permissions::$palletes, $permissions);
        });    
    
    }
}
