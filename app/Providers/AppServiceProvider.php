<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        if (!App::environment('local'))
            URL::forceScheme('https');
        //
        if ($this->app->environment() !== 'production') {
            \Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
                \Log::info([
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
