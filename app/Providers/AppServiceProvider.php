<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\LegacyMd5Hasher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('hash', fn ($app) => new LegacyMd5Hasher());
        $this->app->singleton('hash.driver', fn ($app) => new LegacyMd5Hasher());
    }

    public function boot(): void
    {
        //
    }
}
