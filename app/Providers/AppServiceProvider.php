<?php

namespace App\Providers;

use App\Presentation\Livewire\Notificaciones\ContadorNotificaciones;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Componentes Livewire incrustados en los layouts (<livewire:... />).
        Livewire::component('notificaciones.contador', ContadorNotificaciones::class);
    }
}
