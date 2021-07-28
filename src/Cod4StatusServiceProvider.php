<?php

namespace MTX_GHOST\Cod4Status;

use Illuminate\Support\ServiceProvider;

class Cod4StatusServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/cod4_status.php' => config_path('cod4_status.php'),
        ]);
    }

    public function register()
    {

        // $this->mergeConfigFrom(
        //     __DIR__.'/../config/cod4_status.php',
        //     'cod4_status'
        // );
    }
}
