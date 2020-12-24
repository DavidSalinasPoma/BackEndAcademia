<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class JwtAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Aqui añadimos la clase JwtAuth.php
        require_once app_path() . '/Helpers/JwtAuth.php';
        // Luego cargar el privider en la configuracion del framework para q funcione
        // carpeta config
        // archivo app
        // añadimos App\Providers\JwtAuthServiceProvider::class en provides. eso es todo.
        // Añadimos un alias de la clase en app
        // 'JwtAuth' => App\Helpers\JwtAuth::class, en class aliass
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}