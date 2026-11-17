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

    /** Enlaces: un campo por enlace, se agregan con "+ Agregar enlace". */
    public array $enlaces = [];

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
        $this->enlaces = $datos->links;
        $this->conocimientos = $datos->knowledge;
    }

    public function agregarEnlace(): void
    {
        if (count($this->enlaces) >= ManageAcademicProfile::MAX_LINKS) {
            $this->addError('enlaces', 'Puedes agregar como máximo '.ManageAcademicProfile::MAX_LINKS.' enlaces.');

            return;
        }

        $this->enlaces[] = '';
    }

    public function quitarEnlace(int $indice): void
    {
        unset($this->enlaces[$indice]);
        $this->enlaces = array_values($this->enlaces);
        $this->resetErrorBag();
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
            'enlaces' => ['array', 'max:'.ManageAcademicProfile::MAX_LINKS],
            'enlaces.*' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'conocimientos' => ['array', 'max:'.ManageAcademicProfile::MAX_KNOWLEDGE],
        ], [
            'enlaces.*.url' => 'Escribe la dirección completa, empezando con http:// o https://',
            'enlaces.*.max' => 'El enlace no puede superar los 255 caracteres.',
            'enlaces.max' => 'Puedes agregar como máximo '.ManageAcademicProfile::MAX_LINKS.' enlaces.',
        ], ['biografia' => 'biografía', 'conocimientos' => 'conocimientos']);

        try {
            $this->perfil->save($this->idTutor(), $this->biografia, array_values($this->enlaces), $this->conocimientos);

            // Mostrar la lista ya limpia (sin vacíos ni duplicados).
            $this->enlaces = $this->perfil->get($this->idTutor())->links;
            session()->flash('perfil_ok', 'Tu perfil académico se guardó correctamente.');
        } catch (InvalidProfileData) {
            $this->addError('enlaces', 'Revisa los enlaces: cada uno debe ser una dirección completa que empiece con http:// o https://.');
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
