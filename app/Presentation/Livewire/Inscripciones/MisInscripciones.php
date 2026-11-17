<?php

namespace App\Presentation\Livewire\Inscripciones;

use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\CourseNotAvailable;
use App\Business\Inscripciones\Exceptions\EnrollmentNotFound;
use App\Business\Inscripciones\Exceptions\EnrollmentRuleViolation;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;
use App\Business\Inscripciones\UseCases\CancelEnrollment;
use App\Business\Inscripciones\UseCases\ConfirmParticipation;
use App\Business\Inscripciones\UseCases\ListStudentEnrollments;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** CU14 Gestionar inscripción (vista del estudiante). */
final class MisInscripciones extends Component
{
    /** activas | historial */
    public string $vista = 'activas';

    public ?string $cancelandoCurso = null;

    public string $motivo = '';

    protected ListStudentEnrollments $listEnrollments;
    protected CancelEnrollment $cancelEnrollment;
    protected ConfirmParticipation $confirmParticipation;

    public function boot(
        ListStudentEnrollments $listEnrollments,
        CancelEnrollment $cancelEnrollment,
        ConfirmParticipation $confirmParticipation,
    ): void {
        $this->listEnrollments = $listEnrollments;
        $this->cancelEnrollment = $cancelEnrollment;
        $this->confirmParticipation = $confirmParticipation;
    }

    protected function studentId(): string
    {
        return (string) auth()->id();
    }

    public function abrirCancelar(string $courseId): void
    {
        $this->resetValidation();
        $this->motivo = '';
        $this->cancelandoCurso = $courseId;
    }

    public function cerrarCancelar(): void
    {
        $this->cancelandoCurso = null;
        $this->resetValidation();
    }

    public function cancelarInscripcion(): void
    {
        $this->validate(['motivo' => ['required', 'string', 'min:5', 'max:500']], [
            'motivo.required' => 'Indica el motivo de la cancelación.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        try {
            $this->cancelEnrollment->execute($this->studentId(), (string) $this->cancelandoCurso, $this->motivo);
            $this->cerrarCancelar();
            session()->flash('success', 'La inscripción fue cancelada y se liberó tu cupo.');
        } catch (EnrollmentRuleViolation $e) {
            $this->addError('motivo', $e->getMessage());
        } catch (AttendanceAlreadyVerified|EnrollmentNotFound|CourseNotAvailable $e) {
            $this->cerrarCancelar();
            $this->addError('general', $e->getMessage());
        } catch (StudentNotEligible) {
            abort(403);
        }
    }

    public function confirmarParticipacion(string $courseId): void
    {
        try {
            $this->confirmParticipation->execute($this->studentId(), $courseId);
            session()->flash('success', '¡Gracias! Confirmaste tu participación. Ya puedes calificar al tutor.');
        } catch (EnrollmentRuleViolation|EnrollmentNotFound $e) {
            $this->addError('general', $e->getMessage());
        } catch (StudentNotEligible) {
            abort(403);
        }
    }

    public function render(): View
    {
        $todas = $this->listEnrollments->execute($this->studentId());
        $activas = array_values(array_filter($todas, fn ($i) => $i['estado'] === 'INSCRITO'));
        $historial = array_values(array_filter($todas, fn ($i) => $i['estado'] !== 'INSCRITO'));

        return view('livewire.inscripciones.mis-inscripciones', [
            'inscripciones' => $this->vista === 'historial' ? $historial : $activas,
            'totalActivas' => count($activas),
            'totalHistorial' => count($historial),
        ])->layout('layouts.student')->title('Mis inscripciones');
    }
}
