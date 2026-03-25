<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuditService;

class AuditServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AuditService::class, function ($app) {
            return new AuditService();
        });
    }

    public function boot()
    {
        //
    }
}