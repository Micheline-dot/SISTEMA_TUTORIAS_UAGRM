<?php

namespace App\Presentation\Livewire\Cursos;

use App\Business\Cursos\DTO\PublicCourseDetail;
use App\Business\Cursos\UseCases\CourseNotFound;
use App\Business\Cursos\UseCases\GetPublicCourseDetail;
use App\Business\Inscripciones\Exceptions\AlreadyEnrolled;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\CannotEnrollOwnCourse;
use App\Business\Inscripciones\Exceptions\CourseFull;
use App\Business\Inscripciones\Exceptions\CourseNotAvailable;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;
use App\Business\Inscripciones\UseCases\EnrollStudent;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** CU13 Visualizar detalles del curso (+ inscripción, CU14). */
final class DetalleTutoria extends Component
{
    public string $courseId;

    protected GetPublicCourseDetail $detalle;

    protected EnrollStudent $inscribir;

    public function boot(GetPublicCourseDetail $detalle, EnrollStudent $inscribir): void
    {
        $this->detalle = $detalle;
        $this->inscribir = $inscribir;
    }

    public function mount(string $courseId): void
    {
        $this->courseId = $courseId;
        $this->cargar();
    }

    private function cargar(): PublicCourseDetail
    {
        try {
            return $this->detalle->execute((string) auth()->id(), $this->courseId);
        } catch (CourseNotFound) {
            abort(404);
        }
    }

    public function inscribirme(): void
    {
        try {
            $this->inscribir->execute((string) auth()->id(), $this->courseId);
            session()->flash('ok', '¡Te inscribiste correctamente! Ya puedes ver los recursos del temario.');
        } catch (AlreadyEnrolled|AttendanceAlreadyVerified|CourseFull|CourseNotAvailable|CannotEnrollOwnCourse|\App\Business\Inscripciones\Exceptions\EnrollmentRuleViolation $e) {
            $this->addError('general', $e->getMessage());
        } catch (StudentNotEligible) {
            abort(403);
        }
    }

    public function render(): View
    {
        $d = $this->cargar();

        return view('livewire.cursos.detalle-tutoria', ['d' => $d])
            ->layout('layouts.student')
            ->title($d->course->title ?: 'Detalle de tutoría');
    }
}
