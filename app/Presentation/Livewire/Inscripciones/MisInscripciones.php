<?php

namespace App\Presentation\Livewire\Inscripciones;

use App\Business\Inscripciones\UseCases\ListStudentEnrollments;
use App\Business\Inscripciones\UseCases\CancelEnrollment;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class MisInscripciones extends Component
{
    protected ListStudentEnrollments $listEnrollments;
    protected CancelEnrollment $cancelEnrollment;

    public function boot(
        ListStudentEnrollments $listEnrollments,
        CancelEnrollment $cancelEnrollment,
    ): void {
        $this->listEnrollments = $listEnrollments;
        $this->cancelEnrollment = $cancelEnrollment;
    }

    public function mount(): void
    {
        $roles = auth()->user()->nombresRoles();

        abort_unless(in_array('ESTUDIANTE', $roles, true), 403);
    }

    protected function studentId(): string
    {
        return (string) auth()->id();
    }

    public function cancelarInscripcion(string $courseId): void
    {
        try {
            $this->cancelEnrollment->execute(
                $this->studentId(),
                $courseId
            );

            session()->flash(
                'success',
                'La inscripción fue cancelada correctamente.'
            );
        } catch (AttendanceAlreadyVerified $e) {
            $this->addError('general', $e->getMessage());
        } catch (EnrollmentNotFound $e) {
            $this->addError('general', $e->getMessage());
        } catch (StudentNotEligible $e) {
            abort(403);
        } catch (\Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'No se pudo cancelar la inscripción. Inténtalo nuevamente.'
            );
        }
    }

    public function render(): View
    {
        return view('livewire.inscripciones.mis-inscripciones', [
            'inscripciones' => $this->listEnrollments->execute(
                $this->studentId()
            ),
        ])->layout('layouts.student')->title('Mis inscripciones');
    }
}