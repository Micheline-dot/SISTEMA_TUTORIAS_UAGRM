<?php

namespace App\Presentation\Livewire\Cursos;

use App\Business\Categorias\UseCases\ManageCategories;
use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CreateCourseDraftInput;
use App\Business\Cursos\DTO\InvalidCourseDraftData;
use App\Business\Cursos\DTO\ListTutorCoursesInput;
use App\Business\Cursos\UseCases\AcademicProfileIncomplete;
use App\Business\Cursos\UseCases\CreateCourseDraft;
use App\Business\Cursos\UseCases\ListTutorCourses;
use App\Business\Cursos\UseCases\ManageCourseCover;
use App\Business\Usuarios\UseCases\ManageAcademicProfile;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * CU07 Gestionar cursos: lista de tutorías del tutor y creación de borradores.
 * Cada tutoría se gestiona en su propia pantalla (GestionarTutoria).
 */
final class MisTutorias extends Component
{
    use WithFileUploads;

    /** (+) Portada opcional al crear la tutoría. */
    public $portada = null;

    public bool $mostrarNueva = false;

    public string $titulo = '';

    public string $categoria = '';

    public string $filtro = '';

    protected CreateCourseDraft $createCourse;
    protected ListTutorCourses $listCourses;
    protected ManageCategories $categorias;
    protected ManageAcademicProfile $perfilAcademico;
    protected ManageCourseCover $portadas;

    public function boot(
        CreateCourseDraft $createCourse,
        ListTutorCourses $listCourses,
        ManageCategories $categorias,
        ManageAcademicProfile $perfilAcademico,
        ManageCourseCover $portadas,
    ): void {
        $this->portadas = $portadas;
        $this->createCourse = $createCourse;
        $this->listCourses = $listCourses;
        $this->categorias = $categorias;
        $this->perfilAcademico = $perfilAcademico;
    }

    protected function tutorId(): string
    {
        return (string) auth()->id();
    }

    public function nuevaTutoria(): void
    {
        $this->reset('titulo', 'categoria', 'portada');
        $this->resetValidation();
        $this->mostrarNueva = true;
    }

    public function cerrarNueva(): void
    {
        $this->mostrarNueva = false;
        $this->resetValidation();
    }

    public function crear()
    {
        $this->validate([
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'categoria' => ['required', 'string'],
            'portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.ManageCourseCover::MAX_KILOBYTES],
        ], [
            'portada.image' => 'La portada debe ser una imagen.',
            'portada.mimes' => 'La portada debe ser JPG, PNG o WEBP.',
            'portada.max' => 'La portada no debe superar 3 MB.',
        ], ['titulo' => 'título', 'categoria' => 'categoría']);

        try {
            $course = $this->createCourse->execute(new CreateCourseDraftInput(
                tutorId: $this->tutorId(),
                course: new CourseDraftData(
                    title: trim($this->titulo),
                    isFree: true,
                    categoryId: $this->categoria,
                ),
            ));
        } catch (AcademicProfileIncomplete) {
            $this->addError('titulo', 'Primero completa tu perfil académico (biografía y conocimientos).');

            return null;
        } catch (InvalidCourseDraftData) {
            $this->addError('categoria', 'Selecciona una categoría válida.');

            return null;
        }

        $mensaje = 'Borrador creado. Completa los datos y el temario para publicarlo.';
        if ($this->portada !== null) {
            try {
                $this->portadas->update($this->tutorId(), $course->id, $this->portada->getRealPath(), $this->portada->extension());
            } catch (\Throwable) {
                $mensaje .= ' No se pudo guardar la portada; súbela desde esta pantalla.';
            }
        }

        session()->flash('ok', $mensaje);

        return $this->redirectRoute('tutor.tutoria', ['courseId' => $course->id], navigate: true);
    }

    public function render(): View
    {
        $tutorias = $this->listCourses->execute(new ListTutorCoursesInput($this->tutorId()));

        if ($this->filtro !== '') {
            $tutorias = array_values(array_filter($tutorias, fn ($t) => $t->status->value === $this->filtro));
        }

        return view('livewire.cursos.mis-tutorias', [
            'tutorias' => $tutorias,
            'categorias' => $this->categorias->list(),
            'perfilCompleto' => $this->perfilAcademico->get($this->tutorId())->isComplete(),
        ])->layout('layouts.dashboard')->title('Mis tutorías');
    }
}
