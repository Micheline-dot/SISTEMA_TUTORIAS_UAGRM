<?php

namespace App\Presentation\Livewire\Habilitaciones;

use App\Business\Habilitaciones\Exceptions\DatosAcademicosIncompletos;
use App\Business\Habilitaciones\Exceptions\SolicitudPendienteExistente;
use App\Business\Habilitaciones\Exceptions\SolicitudYaAprobada;
use App\Business\Habilitaciones\UseCases\ConsultarMisSolicitudesHabilitacion;
use App\Business\Habilitaciones\UseCases\RegistrarSolicitudHabilitacion;
use App\Business\Usuarios\DTO\UserProfile;
use App\Business\Usuarios\UseCases\GetUserProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * CU03 Solicitar habilitación como tutor.
 * Exige haber completado los datos académicos en "Mi perfil"; el tipo de
 * personal y el código se toman de ahí y no se pueden cambiar en la solicitud.
 */
final class SolicitarHabilitacion extends Component
{
    use WithFileUploads;

    public $curriculum;
    public $documentoRespaldo;
    public $cartaSolicitud;
    public $registroAcademico;
    public $historialAcademico;

    protected GetUserProfile $getProfile;

    protected ConsultarMisSolicitudesHabilitacion $misSolicitudes;

    public function boot(GetUserProfile $getProfile, ConsultarMisSolicitudesHabilitacion $misSolicitudes): void
    {
        $this->getProfile = $getProfile;
        $this->misSolicitudes = $misSolicitudes;
    }

    private function perfil(): UserProfile
    {
        return $this->getProfile->execute((string) Auth::id());
    }

    public function enviar(RegistrarSolicitudHabilitacion $casoUso): void
    {
        $perfil = $this->perfil();

        if (! $perfil->academicDataCompleted()) {
            session()->flash('error', 'Antes de solicitar ser tutor debes registrar tus datos académicos en "Mi perfil".');

            return;
        }

        $tipo = $perfil->registrationType;
        $pdf = 'required|file|mimes:pdf|max:5120';

        $reglas = ['curriculum' => $pdf];

        if ($tipo === 'AUXILIAR') {
            $reglas['documentoRespaldo'] = $pdf;
        }

        if ($tipo === 'ESTUDIANTE') {
            $reglas['cartaSolicitud'] = $pdf;
            $reglas['registroAcademico'] = $pdf;
            $reglas['historialAcademico'] = $pdf;
        }

        $this->validate($reglas);

        $archivos = [];

        try {
            $archivos['curriculum'] = $this->curriculum
                ->store('habilitaciones/curriculum', 'local');

            if ($tipo === 'AUXILIAR') {
                $archivos['documento_respaldo'] = $this->documentoRespaldo
                    ->store('habilitaciones/respaldo', 'local');
            }

            if ($tipo === 'ESTUDIANTE') {
                $archivos['carta_solicitud'] = $this->cartaSolicitud
                    ->store('habilitaciones/cartas', 'local');

                $archivos['registro_academico'] = $this->registroAcademico
                    ->store('habilitaciones/registros', 'local');

                $archivos['historial_academico'] = $this->historialAcademico
                    ->store('habilitaciones/historiales', 'local');
            }

            $casoUso->ejecutar((int) Auth::id(), $archivos);

            session()->flash('mensaje', 'Solicitud enviada correctamente. Está pendiente de revisión.');

            $this->redirectRoute('habilitacion.solicitar', navigate: true);
        } catch (\Throwable $error) {
            foreach ($archivos as $ruta) {
                if (is_string($ruta) && str_starts_with($ruta, 'habilitaciones/')) {
                    Storage::disk('local')->delete($ruta);
                }
            }

            if ($error instanceof DatosAcademicosIncompletos
                || $error instanceof SolicitudPendienteExistente
                || $error instanceof SolicitudYaAprobada) {
                session()->flash('error', $error->getMessage());

                return;
            }

            throw $error;
        }
    }

    public function render()
    {
        $solicitudes = $this->misSolicitudes->ejecutar((int) Auth::id());

        return view('livewire.habilitaciones.solicitar-habilitacion', [
            'perfil' => $this->perfil(),
            'solicitudes' => $solicitudes,
            'tienePendiente' => ConsultarMisSolicitudesHabilitacion::tieneEstado($solicitudes, 'PENDIENTE'),
            // "Aprobada" = es tutor habilitado hoy (si fue degradado puede volver a solicitar).
            'tieneAprobada' => Auth::user()->tieneRol('TUTOR'),
        ])->layout('layouts.student')->title('Quiero ser tutor');
    }
}
