<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $settings = DB::table('settings')->pluck('settings_value', 'settings_key');
                $this->app->singleton('settings', function ($app) use ($settings) {
                    return $settings;
                });
            }
        } catch (QueryException $e) {
            // handle the exception here, e.g. log it or show an error message
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
