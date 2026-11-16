<?php

namespace App\Presentation\Livewire\Notificaciones;

use App\Business\Notificaciones\UseCases\GestionarMisNotificaciones;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/** CU19: campana con el número de notificaciones no leídas (barra superior). */
final class ContadorNotificaciones extends Component
{
    protected GestionarMisNotificaciones $notificaciones;

    public function boot(GestionarMisNotificaciones $notificaciones): void
    {
        $this->notificaciones = $notificaciones;
    }

    #[On('notificaciones-actualizadas')]
    public function refrescar(): void
    {
        // Solo re-renderiza.
    }

    public function render(): View
    {
        return view('livewire.notificaciones.contador', [
            'noLeidas' => $this->notificaciones->contarNoLeidas((string) auth()->id()),
        ]);
    }
}
