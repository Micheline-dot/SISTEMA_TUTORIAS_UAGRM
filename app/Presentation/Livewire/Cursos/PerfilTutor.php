<?php

namespace App\Presentation\Livewire\Cursos;

use App\Business\Cursos\UseCases\GetTutorPublicProfile;
use App\Business\Cursos\UseCases\TutorNotEnabled;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** (+) Perfil público del tutor: datos académicos, reseñas y sus tutorías publicadas. */
final class PerfilTutor extends Component
{
    public string $tutorId;

    protected GetTutorPublicProfile $perfil;

    public function boot(GetTutorPublicProfile $perfil): void
    {
        $this->perfil = $perfil;
    }

    public function mount(string $tutorId): void
    {
        $this->tutorId = $tutorId;
    }

    public function render(): View
    {
        try {
            $p = $this->perfil->execute($this->tutorId);
        } catch (TutorNotEnabled) {
            abort(404);
        }

        return view('livewire.cursos.perfil-tutor', ['p' => $p])
            ->layout('layouts.student')
            ->title('Perfil de '.$p['nombre']);
    }
}
