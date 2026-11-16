<?php

namespace App\Presentation\Livewire\Tutor;

use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\UseCases\ManageAcademicProfile;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** CU06 Gestionar perfil académico del tutor. */
final class PerfilAcademico extends Component
{
    public string $biografia = '';

    /** Un enlace por línea. */
    public string $enlaces = '';

    /** Conocimientos agregados como etiquetas. */
    public array $conocimientos = [];

    public string $nuevoConocimiento = '';

    protected ManageAcademicProfile $perfil;

    public function boot(ManageAcademicProfile $perfil): void
    {
        $this->perfil = $perfil;
    }

    public function mount(): void
    {
        $datos = $this->perfil->get($this->idTutor());

        $this->biografia = $datos->biography ?? '';
        $this->enlaces = implode("\n", $datos->links);
        $this->conocimientos = $datos->knowledge;
    }

    public function agregarConocimiento(): void
    {
        $item = trim($this->nuevoConocimiento);

        $this->resetErrorBag('nuevoConocimiento');

        if (mb_strlen($item) < 2 || mb_strlen($item) > 60) {
            $this->addError('nuevoConocimiento', 'Escribe entre 2 y 60 caracteres.');

            return;
        }

        if (count($this->conocimientos) >= ManageAcademicProfile::MAX_KNOWLEDGE) {
            $this->addError('nuevoConocimiento', 'Máximo '.ManageAcademicProfile::MAX_KNOWLEDGE.' conocimientos.');

            return;
        }

        foreach ($this->conocimientos as $existente) {
            if (mb_strtolower($existente) === mb_strtolower($item)) {
                $this->nuevoConocimiento = '';

                return;
            }
        }

        $this->conocimientos[] = $item;
        $this->nuevoConocimiento = '';
    }

    public function quitarConocimiento(int $indice): void
    {
        unset($this->conocimientos[$indice]);
        $this->conocimientos = array_values($this->conocimientos);
    }

    public function guardar(): void
    {
        $this->validate([
            'biografia' => ['nullable', 'string', 'max:'.ManageAcademicProfile::MAX_BIOGRAPHY],
            'enlaces' => ['nullable', 'string', 'max:2000'],
            'conocimientos' => ['array', 'max:'.ManageAcademicProfile::MAX_KNOWLEDGE],
        ], [], ['biografia' => 'biografía', 'enlaces' => 'enlaces', 'conocimientos' => 'conocimientos']);

        $lista = preg_split('/\r\n|\r|\n/', $this->enlaces) ?: [];

        try {
            $this->perfil->save($this->idTutor(), $this->biografia, $lista, $this->conocimientos);
            session()->flash('perfil_ok', 'Tu perfil académico se guardó correctamente.');
        } catch (InvalidProfileData $e) {
            $this->addError('enlaces', str_starts_with($e->getMessage(), 'Invalid link')
                ? 'Revisa los enlaces: cada línea debe ser una dirección completa que empiece con http:// o https://. Máximo '.ManageAcademicProfile::MAX_LINKS.'.'
                : 'Revisa los datos ingresados.');
        }
    }

    private function idTutor(): string
    {
        return (string) auth()->id();
    }

    public function render(): View
    {
        $completo = trim($this->biografia) !== '' && $this->conocimientos !== [];

        return view('livewire.tutor.perfil-academico', ['completo' => $completo])
            ->layout('layouts.dashboard')
            ->title('Perfil académico');
    }
}
