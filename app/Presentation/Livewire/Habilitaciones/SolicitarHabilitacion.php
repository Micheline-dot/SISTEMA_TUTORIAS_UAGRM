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
 * CU03 Solicitar habilitación como tutor — asistente de 3 pasos:
 *   1. Elegir tipo (Soy docente / auxiliar / estudiante).
 *   2. Cargar información (instrucciones y documentos de ese tipo).
 *   3. Enviar a validar (revisión y declaración).
 * El tipo debe coincidir con el registrado en "Mi perfil" (CU02); el código
 * docente / de auxiliar se toma de ahí.
 */
final class SolicitarHabilitacion extends Component
{
    use WithFileUploads;

    /** Documentos por tipo: propiedad => [clave en negocio, carpeta, etiqueta, ayuda]. */
    public const DOCUMENTOS = [
        'DOCENTE' => [
            'curriculum' => ['curriculum', 'habilitaciones/curriculum', 'Currículum vitae', 'Formación, experiencia docente y áreas que puedes enseñar.'],
        ],
        'AUXILIAR' => [
            'curriculum' => ['curriculum', 'habilitaciones/curriculum', 'Currículum vitae', 'Formación y experiencia como auxiliar.'],
            'documentoRespaldo' => ['documento_respaldo', 'habilitaciones/respaldo', 'Documento de respaldo', 'Designación, certificado o constancia de tu auxiliatura.'],
        ],
        'ESTUDIANTE' => [
            'cartaSolicitud' => ['carta_solicitud', 'habilitaciones/cartas', 'Carta de solicitud', 'Explica por qué quieres ser tutor y en qué materias.'],
            'curriculum' => ['curriculum', 'habilitaciones/curriculum', 'Currículum vitae', 'Tu formación y experiencia (puede ser breve).'],
            'registroAcademico' => ['registro_academico', 'habilitaciones/registros', 'Registro académico', 'Documento que acredita tu inscripción en la carrera.'],
            'historialAcademico' => ['historial_academico', 'habilitaciones/historiales', 'Historial académico', 'Tus calificaciones de las materias aprobadas.'],
        ],
    ];

    private const MENSAJES = [
        'required' => 'Carga el archivo de :attribute.',
        'file' => 'Carga el archivo de :attribute.',
        'mimes' => 'El archivo de :attribute debe ser PDF.',
        'max' => 'El archivo de :attribute no puede superar 5 MB.',
    ];

    public int $paso = 1;

    public string $tipo = '';

    public bool $declaracion = false;

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

    // ---------- Paso 1: elegir tipo ----------

    public function elegirTipo(string $tipo): void
    {
        $perfil = $this->perfil();

        if (! isset(self::DOCUMENTOS[$tipo])) {
            return;
        }

        if ($tipo !== $perfil->registrationType) {
            $this->addError('tipo', 'Tu perfil está registrado como '.mb_strtolower($perfil->registrationType)
                .'. Solo puedes solicitar con ese tipo.');

            return;
        }

        $this->resetValidation();
        $this->tipo = $tipo;
        $this->paso = 2;
    }

    // ---------- Paso 2: cargar información ----------

    /** @return array<string, string> */
    private function reglasDocumentos(): array
    {
        return array_map(
            static fn (): string => 'required|file|mimes:pdf|max:5120',
            self::DOCUMENTOS[$this->tipo] ?? [],
        );
    }

    /** @return array<string, string> */
    private function nombresDocumentos(): array
    {
        return array_map(static fn (array $d): string => mb_strtolower($d[2]), self::DOCUMENTOS[$this->tipo] ?? []);
    }

    public function irARevision(): void
    {
        $this->validate($this->reglasDocumentos(), self::MENSAJES, $this->nombresDocumentos());

        $this->paso = 3;
    }

    public function volver(int $paso): void
    {
        $this->resetValidation();
        $this->paso = max(1, min($paso, $this->paso));

        if ($this->paso === 1) {
            $this->tipo = '';
        }
    }

    // ---------- Paso 3: enviar a validar ----------

    public function enviar(RegistrarSolicitudHabilitacion $casoUso): void
    {
        if ($this->tipo === '' || $this->tipo !== $this->perfil()->registrationType) {
            $this->volver(1);

            return;
        }

        $this->validate(
            [...$this->reglasDocumentos(), 'declaracion' => 'accepted'],
            [...self::MENSAJES, 'declaracion.accepted' => 'Debes confirmar que la información es verídica.'],
            $this->nombresDocumentos(),
        );

        $archivos = [];

        try {
            foreach (self::DOCUMENTOS[$this->tipo] as $propiedad => [$clave, $carpeta]) {
                $archivos[$clave] = $this->{$propiedad}->store($carpeta, 'local');
            }

            $casoUso->ejecutar((int) Auth::id(), ['tipo_personal' => $this->tipo, ...$archivos]);

            session()->flash('mensaje', 'Solicitud enviada a validar. Te avisaremos cuando el administrador la revise.');

            $this->redirectRoute('habilitacion.solicitar', navigate: true);
        } catch (\Throwable $error) {
            foreach ($archivos as $ruta) {
                if (is_string($ruta) && str_starts_with($ruta, 'habilitaciones/')) {
                    Storage::disk('local')->delete($ruta);
                }
            }

            if ($error instanceof DatosAcademicosIncompletos
                || $error instanceof SolicitudPendienteExistente
                || $error instanceof SolicitudYaAprobada
                || $error instanceof \InvalidArgumentException) {
                session()->flash('error', $error->getMessage());
                $this->volver(1);

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
            'documentos' => self::DOCUMENTOS,
        ])->layout('layouts.student')->title('Quiero ser tutor');
    }
}
