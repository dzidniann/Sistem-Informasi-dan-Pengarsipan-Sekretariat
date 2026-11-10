<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Use the request scheme supplied by the explicitly trusted proxy.
        // Production may also be commissioned over a local HTTP backend.
    }
}
