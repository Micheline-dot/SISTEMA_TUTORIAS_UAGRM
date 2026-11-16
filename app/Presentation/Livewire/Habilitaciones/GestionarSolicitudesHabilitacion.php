<?php

namespace App\Presentation\Livewire\Habilitaciones;

use App\Business\Habilitaciones\UseCases\AprobarSolicitudHabilitacion;
use App\Business\Habilitaciones\UseCases\RechazarSolicitudHabilitacion;
use App\Business\Habilitaciones\UseCases\ListarSolicitudesHabilitacionPendientes;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class GestionarSolicitudesHabilitacion extends Component
{
    protected ListarSolicitudesHabilitacionPendientes $listarSolicitudes;

    public string $observacion = '';

    public ?int $solicitudSeleccionada = null;

    public string $accion = '';

    public function boot(
        ListarSolicitudesHabilitacionPendientes $listarSolicitudes
    ): void {
        $this->listarSolicitudes = $listarSolicitudes;
    }

    public function mount(): void
    {
        abort_unless(
            Auth::check()
            && Auth::user()->tieneRol('ADMINISTRADOR'),
            403,
            'No tienes autorización para acceder a esta sección.'
        );
    }

    public function aprobar(
        int $idSolicitud,
        AprobarSolicitudHabilitacion $casoUso
    ): void {
        try {
            $casoUso->ejecutar(
                $idSolicitud,
                (int) Auth::id()
            );

            session()->flash(
                'mensaje',
                'La solicitud fue aprobada correctamente.'
            );
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            session()->flash(
                'error',
                'Ocurrió un error al aprobar la solicitud.'
            );
        }
    }

    public function seleccionarRechazo(int $idSolicitud): void
    {
        $this->solicitudSeleccionada = $idSolicitud;
        $this->observacion = '';
        $this->accion = 'rechazar';
    }

    public function rechazar(
        RechazarSolicitudHabilitacion $casoUso
    ): void {
        $this->validate([
            'observacion' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ], [
            'observacion.required' => 'Debes indicar el motivo del rechazo.',
            'observacion.min' => 'El motivo debe tener al menos 5 caracteres.',
            'observacion.max' => 'El motivo no puede superar los 2000 caracteres.',
        ]);

        if ($this->solicitudSeleccionada === null) {
            session()->flash('error', 'No seleccionaste una solicitud.');
            return;
        }

        try {
            $casoUso->ejecutar(
                $this->solicitudSeleccionada,
                (int) Auth::id(),
                $this->observacion
            );

            $this->cancelarRechazo();

            session()->flash(
                'mensaje',
                'La solicitud fue rechazada correctamente.'
            );
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            session()->flash(
                'error',
                'Ocurrió un error al rechazar la solicitud.'
            );
        }
    }

    public function cancelarRechazo(): void
    {
        $this->solicitudSeleccionada = null;
        $this->observacion = '';
        $this->accion = '';
        $this->resetValidation();
    }

    public function render()
    {
        $solicitudes = $this->listarSolicitudes->ejecutar();

        return view(
            'livewire.habilitaciones.gestionar-solicitudes-habilitacion',
            [
                'solicitudes' => $solicitudes,
            ]
        )->layout('layouts.admin')->title('Solicitudes de tutor');
    }
}