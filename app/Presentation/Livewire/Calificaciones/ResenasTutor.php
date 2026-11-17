<?php

namespace App\Presentation\Livewire\Calificaciones;

use App\Business\Calificaciones\UseCases\GetTutorReviews;
use App\Business\Cursos\DTO\ListTutorCoursesInput;
use App\Business\Cursos\UseCases\ListTutorCourses;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/** CU18 Ver reseñas y formularios (tutor). */
final class ResenasTutor extends Component
{
    #[Url(as: 'tutoria', except: '')]
    public string $curso = '';

    protected GetTutorReviews $resenas;

    protected ListTutorCourses $cursos;

    public function boot(GetTutorReviews $resenas, ListTutorCourses $cursos): void
    {
        $this->resenas = $resenas;
        $this->cursos = $cursos;
    }

    public function render(): View
    {
        $tutorId = (string) auth()->id();
        $cursos = array_values(array_filter(
            $this->cursos->execute(new ListTutorCoursesInput($tutorId)),
            fn ($c) => $c->status->value !== 'BORRADOR',
        ));

        return view('livewire.calificaciones.resenas-tutor', [
            'datos' => $this->resenas->execute($tutorId, $this->curso !== '' ? $this->curso : null),
            'cursos' => $cursos,
        ])->layout('layouts.dashboard')->title('Reseñas y encuestas');
    }
}
