<?php

namespace App\Presentation\Livewire\Administracion;

use App\Business\Configuracion\Exceptions\InvalidLogo;
use App\Business\Configuracion\UseCases\ManageLoginBackground;
use App\Business\Configuracion\UseCases\ManageSystemLogo;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/** (+) El administrador configura el logo institucional y la foto de fondo del login. */
final class ConfigurarLogo extends Component
{
    use WithFileUploads;

    public $logo = null;

    public $fondo = null;

    protected ManageSystemLogo $logos;

    protected ManageLoginBackground $fondos;

    public function boot(ManageSystemLogo $logos, ManageLoginBackground $fondos): void
    {
        $this->logos = $logos;
        $this->fondos = $fondos;
    }

    // ---------- Logo ----------

    public function guardar(): void
    {
        $this->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:'.ManageSystemLogo::MAX_KILOBYTES],
        ], [
            'logo.required' => 'Selecciona una imagen.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser PNG, JPG o WEBP.',
            'logo.max' => 'El logo no debe superar 2 MB.',
        ]);

        try {
            $this->logos->update((string) auth()->id(), $this->logo->getRealPath(), $this->logo->extension());
            $this->reset('logo');
            session()->flash('ok', 'Logo actualizado. Ya se muestra en el inicio de sesión y en el menú.');
        } catch (InvalidLogo $e) {
            $this->addError('logo', $e->getMessage());
        }
    }

    public function quitar(): void
    {
        try {
            $this->logos->remove((string) auth()->id());
            session()->flash('ok', 'Se quitó el logo. Se mostrará el nombre del sistema en texto.');
        } catch (InvalidLogo $e) {
            $this->addError('logo', $e->getMessage());
        }
    }

    // ---------- Fondo del login ----------

    public function guardarFondo(): void
    {
        $this->validate([
            'fondo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:'.ManageLoginBackground::MAX_KILOBYTES],
        ], [
            'fondo.required' => 'Selecciona una imagen.',
            'fondo.image' => 'El archivo debe ser una imagen.',
            'fondo.mimes' => 'El fondo debe ser PNG, JPG o WEBP.',
            'fondo.max' => 'El fondo no debe superar 5 MB.',
        ]);

        try {
            $this->fondos->update((string) auth()->id(), $this->fondo->getRealPath(), $this->fondo->extension());
            $this->reset('fondo');
            session()->flash('ok', 'Fondo actualizado. Ya se muestra detrás del inicio de sesión y del registro.');
        } catch (InvalidLogo $e) {
            $this->addError('fondo', $e->getMessage());
        }
    }

    public function quitarFondo(): void
    {
        try {
            $this->fondos->remove((string) auth()->id());
            session()->flash('ok', 'Se quitó el fondo. El inicio de sesión vuelve al fondo claro.');
        } catch (InvalidLogo $e) {
            $this->addError('fondo', $e->getMessage());
        }
    }

    public function render(): View
    {
        $rutaFondo = $this->fondos->currentPath();

        return view('livewire.administracion.configurar-logo', [
            'tieneLogo' => $this->logos->hasLogo(),
            'urlFondo' => $rutaFondo === null ? null : route('sistema.fondo-login', ['v' => substr(md5($rutaFondo), 0, 8)]),
        ])->layout('layouts.admin')->title('Logo y fondo');
    }
}
