<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //認可の設定
        Gate::define('admin',function($user){
            return $user->role===1;
        });
        Gate::define('manager-higher',function($user){
            return $user->role>0 && $user->role<=5;
        });
        Gate::define('user-higher',function($user){
            return $user->role>0 && $user->role<=9;
        });
    }
}
