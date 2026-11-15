<?php

namespace App\Presentation\Livewire\Habilitaciones;

use App\Business\Habilitaciones\UseCases\RegistrarSolicitudHabilitacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

final class SolicitarHabilitacion extends Component
{
    use WithFileUploads;

    public string $tipoPersonal = '';

    public string $codigoDoc = '';
    public string $codigoAuxiliar = '';

    public $curriculum;
    public $documentoRespaldo;
    public $cartaSolicitud;
    public $registroAcademico;
    public $historialAcademico;

    public function enviar(RegistrarSolicitudHabilitacion $casoUso): void
    {
        $reglas = [
            'tipoPersonal' => 'required|in:DOCENTE,AUXILIAR,ESTUDIANTE',
            'curriculum' => 'required|file|mimes:pdf|max:5120',
        ];

        if ($this->tipoPersonal === 'DOCENTE') {
            $reglas['codigoDoc'] = 'required|string|max:50';
        }

        if ($this->tipoPersonal === 'AUXILIAR') {
            $reglas['codigoAuxiliar'] = 'required|string|max:50';
            $reglas['documentoRespaldo'] = 'required|file|mimes:pdf|max:5120';
        }

        if ($this->tipoPersonal === 'ESTUDIANTE') {
            $reglas['cartaSolicitud'] = 'required|file|mimes:pdf|max:5120';
            $reglas['registroAcademico'] = 'required|file|mimes:pdf|max:5120';
            $reglas['historialAcademico'] = 'required|file|mimes:pdf|max:5120';
        }

        $this->validate($reglas);

        $archivos = [];

        try {
            $archivos['curriculum'] = $this->curriculum
                ->store('habilitaciones/curriculum', 'local');

            if ($this->tipoPersonal === 'DOCENTE') {
                $archivos['codigo_doc'] = $this->codigoDoc;
            }

            if ($this->tipoPersonal === 'AUXILIAR') {
                $archivos['codigo_auxiliar'] = $this->codigoAuxiliar;
                $archivos['documento_respaldo'] = $this->documentoRespaldo
                    ->store('habilitaciones/respaldo', 'local');
            }

            if ($this->tipoPersonal === 'ESTUDIANTE') {
                $archivos['carta_solicitud'] = $this->cartaSolicitud
                    ->store('habilitaciones/cartas', 'local');

                $archivos['registro_academico'] = $this->registroAcademico
                    ->store('habilitaciones/registros', 'local');

                $archivos['historial_academico'] = $this->historialAcademico
                    ->store('habilitaciones/historiales', 'local');
            }

            $casoUso->ejecutar(
                (int) Auth::id(),
                [
                    'tipo_personal' => $this->tipoPersonal,
                    ...$archivos,
                ]
            );

            session()->flash('mensaje', 'Solicitud enviada correctamente. Está pendiente de revisión.');

            $this->redirectRoute('habilitacion.solicitar', navigate: true);
        } catch (\Throwable $error) {
            foreach ($archivos as $campo => $ruta) {
                if (
                    is_string($ruta)
                    && str_starts_with($ruta, 'habilitaciones/')
                ) {
                    Storage::disk('local')->delete($ruta);
                }
            }

            throw $error;
        }
    }

    public function render()
{
    return view('livewire.habilitaciones.solicitar-habilitacion')
        ->layout('layouts.auth');
}
}
