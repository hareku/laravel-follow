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
        $this->mergeConfigFrom(
            __DIR__.'/../config/follow.php', 'follow'
        );

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/2017_05_20_000000_create_follow_relationships_table.php');
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
