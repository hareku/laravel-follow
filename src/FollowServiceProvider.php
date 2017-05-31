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
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/follow.php', 'follow'
        );
    }
}
