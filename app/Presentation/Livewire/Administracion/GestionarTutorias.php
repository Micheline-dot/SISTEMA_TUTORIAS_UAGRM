<?php

namespace App\Presentation\Livewire\Administracion;

use App\Business\Administracion\Exceptions\CancellationReasonRequired;
use App\Business\Administracion\Exceptions\CourseCannotBeCancelled;
use App\Business\Administracion\UseCases\CancelAdminCourse;
use App\Business\Administracion\UseCases\GetAdminCourseDetails;
use App\Business\Administracion\UseCases\ListAdminCourses;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class GestionarTutorias extends Component
{
    protected ListAdminCourses $listarTutorias;

    protected CancelAdminCourse $cancelarCurso;

    protected GetAdminCourseDetails $obtenerDetalles;

    public string $search = '';

    public string $status = '';

    public bool $mostrarModalCancelacion = false;

    public bool $mostrarModalDetalles = false;

    public string $cursoSeleccionado = '';

    public string $motivoCancelacion = '';

    public ?array $detalleTutoria = null;

    public function boot(
        ListAdminCourses $listarTutorias,
        CancelAdminCourse $cancelarCurso,
        GetAdminCourseDetails $obtenerDetalles
    ): void {
        $this->listarTutorias = $listarTutorias;
        $this->cancelarCurso = $cancelarCurso;
        $this->obtenerDetalles = $obtenerDetalles;
    }

    public function mount(): void
    {
        $this->autorizarAdministrador();
    }

    private function autorizarAdministrador(): void
    {
        abort_unless(
            Auth::check()
                && Auth::user()->roles()
                    ->where('nombre', 'ADMINISTRADOR')
                    ->exists(),
            403,
            'No tienes autorización para acceder a esta sección.'
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPageIfAvailable();
    }

    public function limpiarFiltros(): void
    {
        $this->autorizarAdministrador();

        $this->search = '';
        $this->status = '';
    }

    /**
     * Abre los detalles de una tutoría.
     */
    public function verDetalles(string $courseId): void
    {
        $this->autorizarAdministrador();

        $this->resetValidation();

        $this->detalleTutoria = $this->obtenerDetalles->execute($courseId);

        if ($this->detalleTutoria === null) {
            session()->flash(
                'error',
                'No se encontró la tutoría seleccionada.'
            );

            return;
        }

        $this->mostrarModalDetalles = true;
    }

    /**
     * Cierra la ventana de detalles.
     */
    public function cerrarModalDetalles(): void
    {
        $this->autorizarAdministrador();

        $this->mostrarModalDetalles = false;
        $this->detalleTutoria = null;
    }

    public function abrirModalCancelacion(string $courseId): void
    {
        $this->autorizarAdministrador();

        $this->resetValidation();

        $this->cursoSeleccionado = $courseId;
        $this->motivoCancelacion = '';
        $this->mostrarModalCancelacion = true;
    }

    public function cerrarModalCancelacion(): void
    {
        $this->autorizarAdministrador();

        $this->mostrarModalCancelacion = false;
        $this->cursoSeleccionado = '';
        $this->motivoCancelacion = '';

        $this->resetValidation();
    }

    public function cancelarTutoria(): void
    {
        $this->autorizarAdministrador();

        $this->motivoCancelacion = trim($this->motivoCancelacion);

        $this->validate([
            'cursoSeleccionado' => 'required|integer|min:1',
            'motivoCancelacion' => 'required|string|min:5|max:1000',
        ], [
            'motivoCancelacion.required' => 'Debes escribir el motivo de cancelación.',
            'motivoCancelacion.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivoCancelacion.max' => 'El motivo no puede superar los 1000 caracteres.',
        ]);

        try {
            $this->cancelarCurso->execute(
                $this->cursoSeleccionado,
                $this->motivoCancelacion
            );

            $this->mostrarModalCancelacion = false;
            $this->cursoSeleccionado = '';
            $this->motivoCancelacion = '';

            $this->resetValidation();

            session()->flash(
                'success',
                'La tutoría fue cancelada correctamente y sus inscripciones activas también fueron canceladas.'
            );

        } catch (
            CancellationReasonRequired |
            CourseCannotBeCancelled $exception
        ) {
            $this->addError(
                'motivoCancelacion',
                $exception->getMessage()
            );
        }
    }

    private function resetPageIfAvailable(): void
    {
        // Preparado para incorporar paginación posteriormente.
    }

    public function render()
    {
        $tutorias = $this->listarTutorias->execute(
            $this->search,
            $this->status
        );

        return view(
            'livewire.administracion.gestionar-tutorias',
            [
                'tutorias' => $tutorias,
            ]
        )->layout('layouts.admin');
    }
}