<?php

namespace App\Presentation\Livewire\Cursos;

use App\Business\Cursos\DTO\CourseCatalogFilters;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\UseCases\SearchPublishedCourses;
use App\Business\Inscripciones\UseCases\EnrollStudent;
use App\Business\Inscripciones\Exceptions\AlreadyEnrolled;
use App\Business\Inscripciones\Exceptions\AttendanceAlreadyVerified;
use App\Business\Inscripciones\Exceptions\CourseFull;
use App\Business\Inscripciones\Exceptions\CourseNotAvailable;
use App\Business\Inscripciones\Exceptions\StudentNotEligible;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CatalogoTutorias extends Component
{
    public string $search = '';
    public string $modality = '';
    public string $free = '';

    protected SearchPublishedCourses $searchCourses;
    protected EnrollStudent $enrollStudent;

    public function boot(
        SearchPublishedCourses $searchCourses,
        EnrollStudent $enrollStudent,
    ): void {
        $this->searchCourses = $searchCourses;
        $this->enrollStudent = $enrollStudent;
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

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'modality', 'free']);
    }

    public function inscribirme(string $courseId): void
    {
        try {
            $this->enrollStudent->execute(
                $this->studentId(),
                $courseId
            );

            session()->flash(
                'success',
                '¡Tu inscripción se realizó correctamente!'
            );
        } catch (AlreadyEnrolled $e) {
            $this->addError('general', $e->getMessage());
        } catch (AttendanceAlreadyVerified $e) {
            $this->addError('general', $e->getMessage());
        } catch (CourseFull $e) {
            $this->addError('general', $e->getMessage());
        } catch (CourseNotAvailable $e) {
            $this->addError('general', $e->getMessage());
        } catch (StudentNotEligible $e) {
            abort(403);
        } catch (\Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'No se pudo completar la inscripción. Inténtalo nuevamente.'
            );
        }
    }

    public function render(): View
    {
        $filters = new CourseCatalogFilters(
            search: trim($this->search) !== ''
                ? trim($this->search)
                : null,
            modality: $this->modality !== ''
                ? CourseModality::from($this->modality)
                : null,
            isFree: $this->free === ''
                ? null
                : $this->free === '1',
        );

        return view('livewire.cursos.catalogo-tutorias', [
            'tutorias' => $this->searchCourses->execute($filters),
        ])->layout('layouts.student')->title('Buscar tutorías');
    }
}