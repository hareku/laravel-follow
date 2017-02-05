<?php

namespace Hareku\LaravelFollow;

use Illuminate\Support\ServiceProvider;

class FollowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/follow.php' => config_path('follow.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/0000_00_00_000000_create_follow_relationships_table.php' => database_path('migrations/'.date('Y_m_d_His').'_create_follow_relationships_table.php'),
        ], 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
