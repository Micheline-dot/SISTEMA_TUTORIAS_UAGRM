<?php

namespace App\Presentation\Livewire\Cursos;

use App\Business\Categorias\UseCases\ManageCategories;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\DTO\FieldChange;
use App\Business\Cursos\DTO\InvalidCourseDraftData;
use App\Business\Cursos\DTO\UpdateCourseDraftInput;
use App\Business\Cursos\UseCases\CourseNotFound;
use App\Business\Cursos\UseCases\CourseNotOwned;
use App\Business\Cursos\UseCases\CourseRuleViolation;
use App\Business\Cursos\UseCases\ManageCourseCover;
use App\Business\Cursos\UseCases\ManageSyllabus;
use App\Business\Cursos\UseCases\ManageTutorCourse;
use App\Business\Cursos\UseCases\PublishCourseDraft;
use App\Business\Cursos\UseCases\UpdateCourseDraft;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Gestión de una tutoría del tutor:
 *  CU07 datos del borrador y eliminarlo · CU08 temario y recursos ·
 *  CU09 publicar (con validación y confirmación) ·
 *  CU10 cupos/fechas, cancelar con motivo y finalizar.
 */
final class GestionarTutoria extends Component
{
    use WithFileUploads;

    /** (+) Nueva imagen de portada. */
    public $nuevaPortada = null;

    public string $courseId;

    // Datos del curso
    public string $titulo = '';
    public string $descripcion = '';
    public string $categoria = '';
    public string $modalidad = '';
    public string $cupos = '';
    public string $gratuito = '1';
    public string $fechaInicio = '';
    public string $fechaFin = '';

    // Temario
    public string $nuevoTema = '';
    public string $nuevoTemaFecha = '';
    public ?string $editTemaId = null;
    public string $editTemaNombre = '';
    public string $editTemaFecha = '';
    public ?string $recursoTemaId = null;
    public string $recursoNombre = '';
    public string $recursoEnlace = '';

    // Cancelación
    public bool $mostrarCancelar = false;
    public string $motivo = '';

    protected ManageTutorCourse $gestion;
    protected UpdateCourseDraft $actualizarBorrador;
    protected PublishCourseDraft $publicar;
    protected ManageSyllabus $temario;
    protected ManageCategories $categorias;
    protected ManageCourseCover $portadas;

    public function boot(
        ManageTutorCourse $gestion,
        UpdateCourseDraft $actualizarBorrador,
        PublishCourseDraft $publicar,
        ManageSyllabus $temario,
        ManageCategories $categorias,
        ManageCourseCover $portadas,
    ): void {
        $this->portadas = $portadas;
        $this->gestion = $gestion;
        $this->actualizarBorrador = $actualizarBorrador;
        $this->publicar = $publicar;
        $this->temario = $temario;
        $this->categorias = $categorias;
    }

    public function mount(string $courseId): void
    {
        $this->courseId = $courseId;
        $this->cargar($this->curso());
    }

    private function tutorId(): string
    {
        return (string) auth()->id();
    }

    private function curso(): CourseDetails
    {
        try {
            return $this->gestion->details($this->tutorId(), $this->courseId);
        } catch (CourseNotFound|CourseNotOwned) {
            abort(404);
        }
    }

    private function cargar(CourseDetails $c): void
    {
        $this->titulo = $c->title ?? '';
        $this->descripcion = $c->description ?? '';
        $this->categoria = $c->categoryId ?? '';
        $this->modalidad = $c->modality?->value ?? '';
        $this->cupos = $c->totalSeats === null ? '' : (string) $c->totalSeats;
        $this->gratuito = ($c->isFree ?? true) ? '1' : '0';
        $this->fechaInicio = $c->startDate ?? '';
        $this->fechaFin = $c->endDate ?? '';
    }

    // ---------- CU07: datos del borrador ----------

    public function guardarBorrador(): void
    {
        $this->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'categoria' => ['nullable', 'string'],
            'modalidad' => ['nullable', 'in:PRESENCIAL,VIRTUAL,HIBRIDA'],
            'cupos' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'gratuito' => ['required', 'in:0,1'],
            'fechaInicio' => ['nullable', 'date_format:Y-m-d'],
            'fechaFin' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fechaInicio'],
        ], [
            'fechaFin.after_or_equal' => 'La fecha de finalización no puede ser anterior a la de inicio.',
        ], [
            'titulo' => 'título', 'descripcion' => 'descripción', 'cupos' => 'cupos',
            'fechaInicio' => 'fecha de inicio', 'fechaFin' => 'fecha de finalización',
        ]);

        $nullable = static fn (string $v): ?string => trim($v) === '' ? null : trim($v);

        try {
            $this->actualizarBorrador->execute(new UpdateCourseDraftInput(
                tutorId: $this->tutorId(),
                courseId: $this->courseId,
                course: new CourseDraftPatch(
                    title: new FieldChange($nullable($this->titulo)),
                    description: new FieldChange($nullable($this->descripcion)),
                    modality: new FieldChange($this->modalidad === '' ? null : CourseModality::from($this->modalidad)),
                    totalSeats: new FieldChange($this->cupos === '' ? null : (int) $this->cupos),
                    isFree: new FieldChange($this->gratuito === '1'),
                    categoryId: new FieldChange($nullable($this->categoria)),
                    startDate: new FieldChange($nullable($this->fechaInicio)),
                    endDate: new FieldChange($nullable($this->fechaFin)),
                ),
            ));
            session()->flash('ok', 'Datos guardados.');
        } catch (InvalidCourseDraftData) {
            $this->addError('general', 'Revisa los datos: alguna categoría, fecha o valor no es válido.');
        } catch (\App\Business\Cursos\UseCases\CourseNotDraft) {
            $this->addError('general', 'La tutoría ya no es un borrador.');
        }
    }

    public function eliminarBorrador()
    {
        try {
            $this->gestion->deleteDraft($this->tutorId(), $this->courseId);
        } catch (CourseRuleViolation $e) {
            $this->addError('general', $e->getMessage());

            return null;
        }

        session()->flash('ok', 'Borrador eliminado.');

        return $this->redirectRoute('tutor.tutorias', navigate: true);
    }

    // ---------- CU09: publicar ----------

    public function publicarTutoria(): void
    {
        try {
            $this->publicar->execute($this->courseId, $this->tutorId());
            session()->flash('ok', '¡Tu tutoría fue publicada! Ya aparece en la búsqueda de los estudiantes.');
        } catch (CourseRuleViolation $e) {
            $this->addError('general', $e->getMessage().' Falta: '.implode(', ', $e->details).'.');
        } catch (\App\Business\Cursos\UseCases\CourseNotDraft) {
            $this->addError('general', 'La tutoría ya no es un borrador.');
        }
    }

    // ---------- CU10: curso publicado ----------

    public function guardarPublicado(): void
    {
        $this->validate([
            'cupos' => ['required', 'integer', 'min:1', 'max:1000'],
            'fechaInicio' => ['required', 'date_format:Y-m-d'],
            'fechaFin' => ['required', 'date_format:Y-m-d', 'after_or_equal:fechaInicio'],
        ], [
            'fechaFin.after_or_equal' => 'La fecha de finalización no puede ser anterior a la de inicio.',
        ], ['cupos' => 'cupos', 'fechaInicio' => 'fecha de inicio', 'fechaFin' => 'fecha de finalización']);

        try {
            $this->gestion->updatePublished($this->tutorId(), $this->courseId, (int) $this->cupos, $this->fechaInicio, $this->fechaFin);
            session()->flash('ok', 'Cambios guardados. Si cambiaron las fechas, se avisó a los inscritos.');
        } catch (CourseRuleViolation $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    public function abrirCancelar(): void
    {
        $this->motivo = '';
        $this->resetValidation();
        $this->mostrarCancelar = true;
    }

    public function cerrarCancelar(): void
    {
        $this->mostrarCancelar = false;
    }

    public function cancelarTutoria(): void
    {
        $this->validate(['motivo' => ['required', 'string', 'min:5', 'max:500']], [
            'motivo.required' => 'Indica el motivo.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        try {
            $this->gestion->cancel($this->tutorId(), $this->courseId, $this->motivo);
            $this->mostrarCancelar = false;
            session()->flash('ok', 'La tutoría fue cancelada y se avisó a los inscritos.');
        } catch (CourseRuleViolation $e) {
            $this->addError('motivo', $e->getMessage());
        }
    }

    public function finalizarTutoria(): void
    {
        try {
            $this->gestion->finalize($this->tutorId(), $this->courseId);
            session()->flash('ok', 'La tutoría quedó finalizada.');
        } catch (CourseRuleViolation $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    // ---------- (+) Portada ----------

    public function guardarPortada(): void
    {
        $this->validate([
            'nuevaPortada' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.ManageCourseCover::MAX_KILOBYTES],
        ], [
            'nuevaPortada.required' => 'Selecciona una imagen.',
            'nuevaPortada.image' => 'La portada debe ser una imagen.',
            'nuevaPortada.mimes' => 'La portada debe ser JPG, PNG o WEBP.',
            'nuevaPortada.max' => 'La portada no debe superar 3 MB.',
        ]);

        try {
            $this->portadas->update(
                $this->tutorId(),
                $this->courseId,
                $this->nuevaPortada->getRealPath(),
                $this->nuevaPortada->extension(),
            );
            $this->reset('nuevaPortada');
            session()->flash('ok', 'Portada actualizada.');
        } catch (CourseRuleViolation $e) {
            $this->addError('nuevaPortada', $e->getMessage());
        }
    }

    public function quitarPortada(): void
    {
        try {
            $this->portadas->remove($this->tutorId(), $this->courseId);
            session()->flash('ok', 'Se quitó la portada.');
        } catch (CourseRuleViolation $e) {
            $this->addError('nuevaPortada', $e->getMessage());
        }
    }

    // ---------- CU08: temario ----------

    public function agregarTema(): void
    {
        $this->validate([
            'nuevoTema' => ['required', 'string', 'min:2', 'max:150'],
            'nuevoTemaFecha' => ['nullable', 'date_format:Y-m-d'],
        ], [], ['nuevoTema' => 'tema', 'nuevoTemaFecha' => 'fecha']);

        $this->temarioAccion(function (): void {
            $this->temario->addTopic($this->tutorId(), $this->courseId, $this->nuevoTema, $this->nuevoTemaFecha);
            $this->reset('nuevoTema', 'nuevoTemaFecha');
        }, 'nuevoTema');
    }

    public function editarTema(string $id, string $nombre, ?string $fecha): void
    {
        $this->resetValidation();
        $this->editTemaId = $id;
        $this->editTemaNombre = $nombre;
        $this->editTemaFecha = $fecha ?? '';
    }

    public function cancelarEdicionTema(): void
    {
        $this->reset('editTemaId', 'editTemaNombre', 'editTemaFecha');
    }

    public function guardarTema(string $estado): void
    {
        $this->validate([
            'editTemaNombre' => ['required', 'string', 'min:2', 'max:150'],
            'editTemaFecha' => ['nullable', 'date_format:Y-m-d'],
        ], [], ['editTemaNombre' => 'tema', 'editTemaFecha' => 'fecha']);

        $this->temarioAccion(function () use ($estado): void {
            $this->temario->updateTopic($this->tutorId(), $this->courseId, (string) $this->editTemaId, $this->editTemaNombre, $this->editTemaFecha, $estado);
            $this->cancelarEdicionTema();
        }, 'editTemaNombre');
    }

    public function cambiarEstadoTema(string $id, string $nombre, ?string $fecha, string $estado): void
    {
        $this->temarioAccion(fn () => $this->temario->updateTopic($this->tutorId(), $this->courseId, $id, $nombre, $fecha, $estado), 'temario');
    }

    public function eliminarTema(string $id): void
    {
        $this->temarioAccion(fn () => $this->temario->deleteTopic($this->tutorId(), $this->courseId, $id), 'temario');
    }

    public function abrirRecurso(string $temaId): void
    {
        $this->resetValidation();
        $this->reset('recursoNombre', 'recursoEnlace');
        $this->recursoTemaId = $temaId;
    }

    public function cerrarRecurso(): void
    {
        $this->reset('recursoTemaId', 'recursoNombre', 'recursoEnlace');
    }

    public function agregarRecurso(): void
    {
        $this->validate([
            'recursoNombre' => ['required', 'string', 'min:2', 'max:150'],
            'recursoEnlace' => ['required', 'url:http,https', 'max:255'],
        ], ['recursoEnlace.url' => 'Ingresa un enlace completo que empiece con http:// o https://.'],
            ['recursoNombre' => 'nombre del recurso', 'recursoEnlace' => 'enlace']);

        $this->temarioAccion(function (): void {
            $this->temario->addResource($this->tutorId(), $this->courseId, (string) $this->recursoTemaId, $this->recursoNombre, $this->recursoEnlace);
            $this->cerrarRecurso();
        }, 'recursoEnlace');
    }

    public function eliminarRecurso(string $id): void
    {
        $this->temarioAccion(fn () => $this->temario->deleteResource($this->tutorId(), $this->courseId, $id), 'temario');
    }

    private function temarioAccion(callable $accion, string $campoError): void
    {
        try {
            $accion();
        } catch (CourseRuleViolation $e) {
            $this->addError($campoError, $e->getMessage());
        }
    }

    public function render(): View
    {
        $curso = $this->curso();

        return view('livewire.cursos.gestionar-tutoria', [
            'curso' => $curso,
            'temas' => $this->temario->topics($this->tutorId(), $this->courseId),
            'categorias' => $this->categorias->list(),
            'faltantes' => $curso->status === CourseStatus::BORRADOR ? $this->publicar->missingRequirements($curso) : [],
            'editableTemario' => in_array($curso->status, [CourseStatus::BORRADOR, CourseStatus::PUBLICADO], true),
            'hoy' => date('Y-m-d'),
        ])->layout('layouts.dashboard')->title($curso->title ?: 'Gestionar tutoría');
    }
}
