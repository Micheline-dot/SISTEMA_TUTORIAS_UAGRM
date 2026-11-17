<?php

namespace App\Presentation\Livewire\Inscripciones;

use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\UseCases\CourseNotFound;
use App\Business\Cursos\UseCases\CourseNotOwned;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;
use App\Business\Inscripciones\Exceptions\EnrollmentRuleViolation;
use App\Business\Inscripciones\UseCases\ListCourseEnrollments;
use App\Business\Inscripciones\UseCases\RemoveEnrolledStudent;
use App\Business\Inscripciones\UseCases\VerifyAttendance;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** CU15 Gestionar estudiantes inscritos: asistencia, retiro con motivo e historial. */
final class GestionarAsistencia extends Component
{
    public string $courseId;

    public ?string $retirandoId = null;

    public string $motivo = '';

    protected ListCourseEnrollments $listEnrollments;
    protected VerifyAttendance $verifyAttendance;
    protected RemoveEnrolledStudent $removeStudent;

    public function boot(
        ListCourseEnrollments $listEnrollments,
        VerifyAttendance $verifyAttendance,
        RemoveEnrolledStudent $removeStudent,
    ): void {
        $this->listEnrollments = $listEnrollments;
        $this->verifyAttendance = $verifyAttendance;
        $this->removeStudent = $removeStudent;
    }

    public function mount(string $courseId): void
    {
        $this->courseId = $courseId;
        $this->curso();
    }

    protected function tutorId(): string
    {
        return (string) auth()->id();
    }

    private function curso(): CourseDetails
    {
        try {
            return $this->listEnrollments->courseOwnedBy($this->tutorId(), $this->courseId);
        } catch (CourseNotFound|CourseNotOwned) {
            abort(404);
        }
    }

    public function verificar(string $studentId): void
    {
        try {
            $this->verifyAttendance->execute($this->tutorId(), $this->courseId, $studentId);
            session()->flash('success', 'Asistencia registrada. El estudiante podrá confirmar su participación.');
        } catch (AttendanceAlreadyVerified) {
            $this->addError('general', 'La asistencia de este estudiante ya fue registrada.');
        } catch (EnrollmentNotFound) {
            $this->addError('general', 'No se encontró una inscripción activa para este estudiante.');
        }
    }

    public function abrirRetirar(string $studentId): void
    {
        $this->resetValidation();
        $this->motivo = '';
        $this->retirandoId = $studentId;
    }

    public function cerrarRetirar(): void
    {
        $this->retirandoId = null;
        $this->resetValidation();
    }

    public function retirar(): void
    {
        $this->validate(['motivo' => ['required', 'string', 'min:5', 'max:500']], [
            'motivo.required' => 'Indica el motivo del retiro.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        try {
            $this->removeStudent->execute($this->tutorId(), $this->courseId, (string) $this->retirandoId, $this->motivo);
            $this->cerrarRetirar();
            session()->flash('success', 'El estudiante fue retirado y recibió un aviso con el motivo.');
        } catch (EnrollmentRuleViolation $e) {
            $this->addError('motivo', $e->getMessage());
        } catch (AttendanceAlreadyVerified) {
            $this->cerrarRetirar();
            $this->addError('general', 'No puedes retirar a un estudiante cuya asistencia ya fue registrada.');
        } catch (EnrollmentNotFound) {
            $this->cerrarRetirar();
            $this->addError('general', 'El estudiante ya no tiene una inscripción activa.');
        }
    }

    public function render(): View
    {
        $curso = $this->curso();
        $activos = $this->listEnrollments->execute($this->tutorId(), $this->courseId);

        return view('livewire.inscripciones.gestionar-asistencia', [
            'curso' => $curso,
            'estudiantes' => $activos,
            'historial' => $this->listEnrollments->inactive($this->tutorId(), $this->courseId),
            'retirando' => collect($activos)->firstWhere('id_estudiante', $this->retirandoId),
            'puedeGestionar' => in_array($curso->status->value, ['PUBLICADO', 'FINALIZADO'], true),
        ])->layout('layouts.dashboard')->title('Inscritos: '.($curso->title ?? 'tutoría'));
    }
}
