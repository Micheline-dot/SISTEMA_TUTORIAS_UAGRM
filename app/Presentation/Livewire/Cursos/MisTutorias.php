<?php

namespace App\Presentation\Livewire\Cursos;

use Livewire\Component;
use Illuminate\Contracts\View\View;

use App\Business\Cursos\UseCases\CreateCourseDraft;
use App\Business\Cursos\UseCases\ListTutorCourses;
use App\Business\Cursos\UseCases\GetTutorCourseDetails;
use App\Business\Cursos\UseCases\UpdateCourseDraft;
use App\Business\Cursos\UseCases\PublishCourseDraft;

use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\DTO\CreateCourseDraftInput;
use App\Business\Cursos\DTO\ListTutorCoursesInput;
use App\Business\Cursos\DTO\TutorCourseDetailsInput;
use App\Business\Cursos\DTO\UpdateCourseDraftInput;
use App\Business\Cursos\DTO\FieldChange;

final class MisTutorias extends Component
{
    protected CreateCourseDraft $createCourse;
    protected ListTutorCourses $listCourses;
    protected GetTutorCourseDetails $getCourse;
    protected UpdateCourseDraft $updateCourse;
    protected PublishCourseDraft $publishCourse;

    public ?string $courseId = null;

    public string $title = '';
    public string $description = '';
    public string $modality = '';
    public string $totalSeats = '';
    public bool $isFree = true;

    public bool $showForm = false;

    public function boot(
        CreateCourseDraft $createCourse,
        ListTutorCourses $listCourses,
        GetTutorCourseDetails $getCourse,
        UpdateCourseDraft $updateCourse,
        PublishCourseDraft $publishCourse,
    ): void {
        $this->createCourse = $createCourse;
        $this->listCourses = $listCourses;
        $this->getCourse = $getCourse;
        $this->updateCourse = $updateCourse;
        $this->publishCourse = $publishCourse;
    }

    public function mount(): void
    {
        $roles = auth()->user()->nombresRoles();

        abort_unless(in_array('TUTOR', $roles, true), 403);
    }

    protected function tutorId(): string
    {
        return (string) auth()->id();
    }

    public function nuevaTutoria(): void
    {
        $this->resetFormulario();
        $this->showForm = true;
    }

    public function editarTutoria(string $id): void
    {
        try {
            $course = $this->getCourse->execute(
                new TutorCourseDetailsInput(
                    tutorId: $this->tutorId(),
                    courseId: $id,
                )
            );

            if ($course->status !== \App\Business\Cursos\DTO\CourseStatus::BORRADOR) {
                $this->addError('general', 'Solo puedes editar tutorías en estado borrador.');
                return;
            }

            $this->courseId = $course->id;
            $this->title = $course->title ?? '';
            $this->description = $course->description ?? '';
            $this->modality = $course->modality?->value ?? '';
            $this->totalSeats = $course->totalSeats === null
                ? ''
                : (string) $course->totalSeats;
            $this->isFree = $course->isFree ?? true;

            $this->resetValidation();
            $this->showForm = true;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('general', 'No se pudo recuperar la tutoría seleccionada.');
        }
    }

    public function guardarTutoria(): void
    {
        $this->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'modality' => 'nullable|in:PRESENCIAL,VIRTUAL,HIBRIDA',
            'totalSeats' => 'nullable|integer|min:1',
            'isFree' => 'required|boolean',
        ]);

        try {
            $modality = $this->modality === ''
                ? null
                : CourseModality::from($this->modality);

            $seats = $this->totalSeats === ''
                ? null
                : (int) $this->totalSeats;

            if ($this->courseId === null) {
                $this->createCourse->execute(
                    new CreateCourseDraftInput(
                        tutorId: $this->tutorId(),
                        course: new CourseDraftData(
                            title: $this->title !== '' ? $this->title : null,
                            description: $this->description !== '' ? $this->description : null,
                            modality: $modality,
                            totalSeats: $seats,
                            isFree: $this->isFree,
                        ),
                    )
                );

                session()->flash('success', 'La tutoría se guardó como borrador.');
            } else {
                $this->updateCourse->execute(
                    new UpdateCourseDraftInput(
                        tutorId: $this->tutorId(),
                        courseId: $this->courseId,
                        course: new CourseDraftPatch(
                            title: new FieldChange(
                                $this->title !== '' ? $this->title : null
                            ),
                            description: new FieldChange(
                                $this->description !== '' ? $this->description : null
                            ),
                            modality: new FieldChange($modality),
                            totalSeats: new FieldChange($seats),
                            isFree: new FieldChange($this->isFree),
                        ),
                    )
                );

                session()->flash('success', 'La tutoría se actualizó correctamente.');
            }

            $this->resetFormulario();
        } catch (\Throwable $e) {
            report($e);
            $this->addError(
                'general',
                'No se pudo guardar la tutoría. Verifica los datos e inténtalo nuevamente.'
            );
        }
    }

    public function publicarTutoria(string $id): void
    {
        try {
            $this->publishCourse->execute($id, $this->tutorId());

            session()->flash('success', '¡Tu tutoría fue publicada correctamente!');
        } catch (\Throwable $e) {
            report($e);
            $this->addError(
                'general',
                'No se pudo publicar la tutoría. Verifica que siga siendo un borrador.'
            );
        }
    }

    public function cancelarFormulario(): void
    {
        $this->resetFormulario();
    }

    private function resetFormulario(): void
    {
        $this->reset([
            'courseId',
            'title',
            'description',
            'modality',
            'totalSeats',
        ]);

        $this->isFree = true;
        $this->showForm = false;
        $this->resetValidation();
    }

    public function render(): View
    {
        $tutorias = $this->listCourses->execute(
            new ListTutorCoursesInput($this->tutorId())
        );

        return view('livewire.cursos.mis-tutorias', [
            'tutorias' => $tutorias,
        ])->layout('layouts.dashboard')->title('Mis tutorías');
    }
}