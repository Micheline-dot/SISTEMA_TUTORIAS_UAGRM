<?php

namespace App\Presentation\Livewire\Inscripciones;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use App\Business\Inscripciones\UseCases\ListCourseEnrollments;
use App\Business\Inscripciones\UseCases\VerifyAttendance;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;

final class GestionarAsistencia extends Component
{
    protected ListCourseEnrollments $listEnrollments;
    protected VerifyAttendance $verifyAttendance;

    public string $courseId;
    public array $estudiantes = [];

    public function boot(
        ListCourseEnrollments $listEnrollments,
        VerifyAttendance $verifyAttendance
    ): void {
        $this->listEnrollments = $listEnrollments;
        $this->verifyAttendance = $verifyAttendance;
    }

    public function mount(string $courseId): void
    {
        $this->courseId = $courseId;
        $this->cargarEstudiantes();
    }

    protected function tutorId(): string
    {
        return (string) auth()->id();
    }

    public function cargarEstudiantes(): void
    {
        $this->estudiantes = $this->listEnrollments->execute(
            $this->tutorId(),
            $this->courseId
        );
    }

    public function verificar(string $studentId): void
    {
        try {
            $this->verifyAttendance->execute(
                $this->tutorId(),
                $this->courseId,
                $studentId
            );

            session()->flash(
                'success',
                'La asistencia fue verificada correctamente.'
            );

            $this->cargarEstudiantes();
        } catch (AttendanceAlreadyVerified $e) {
            $this->addError('general', 'La asistencia de este estudiante ya fue verificada.');
            $this->cargarEstudiantes();
        } catch (EnrollmentNotFound $e) {
            $this->addError('general', 'No se encontró una inscripción activa para este estudiante.');
            $this->cargarEstudiantes();
        } catch (\Throwable $e) {
            report($e);
            $this->addError('general', 'No se pudo verificar la asistencia. Inténtalo nuevamente.');
        }
    }

    public function render(): View
    {
        return view('livewire.inscripciones.gestionar-asistencia')
            ->layout('layouts.dashboard');
    }
}