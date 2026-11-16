<?php

namespace App\Presentation\Livewire\Notificaciones;

use App\Business\Notificaciones\UseCases\GestionarMisNotificaciones;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** CU19 Recibir notificaciones: bandeja del usuario (todos los roles). */
final class MisNotificaciones extends Component
{
    public bool $soloNoLeidas = false;

    protected GestionarMisNotificaciones $notificaciones;

    public function boot(GestionarMisNotificaciones $notificaciones): void
    {
        $this->notificaciones = $notificaciones;
    }

    public function marcarLeida(string $idNotificacion): void
    {
        $this->notificaciones->marcarLeida($this->idUsuario(), $idNotificacion);
        $this->dispatch('notificaciones-actualizadas');
    }

    public function marcarTodas(): void
    {
        $this->notificaciones->marcarTodasLeidas($this->idUsuario());
        $this->dispatch('notificaciones-actualizadas');
    }

    public function abrir(string $idNotificacion)
    {
        $enlace = $this->notificaciones->abrir($this->idUsuario(), $idNotificacion);
        $this->dispatch('notificaciones-actualizadas');

        // Solo rutas internas (las valida EnviarNotificacion al crearlas).
        if ($enlace !== null && str_starts_with($enlace, '/') && ! str_starts_with($enlace, '//')) {
            return $this->redirect($enlace);
        }

        return null;
    }

    private function idUsuario(): string
    {
        return (string) auth()->id();
    }

    public function render(): View
    {
        $id = $this->idUsuario();

        return view('livewire.notificaciones.mis-notificaciones', [
            'notificaciones' => $this->notificaciones->listar($id, $this->soloNoLeidas),
            'noLeidas' => $this->notificaciones->contarNoLeidas($id),
        ])->layout(auth()->user()->tieneRol('ADMINISTRADOR') ? 'layouts.admin' : 'layouts.student')
            ->title('Notificaciones');
    }
}
