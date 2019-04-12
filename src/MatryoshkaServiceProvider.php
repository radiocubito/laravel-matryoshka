<?php

namespace Radiocubito\Matryoshka;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class MatryoshkaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Blade::directive('cache', function ($expression) {
            return "<?php if (! app('Radiocubito\Matryoshka\BladeDirective')->setUp(__FILE__, {$expression})) : ?>";
        });

        Blade::directive('endcache', function () {
            return "<?php endif; echo app('Radiocubito\Matryoshka\BladeDirective')->tearDown() ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(BladeDirective::class);
    }
}
