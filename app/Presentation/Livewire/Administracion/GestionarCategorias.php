<?php

namespace App\Presentation\Livewire\Administracion;

use App\Business\Categorias\Exceptions\CategoryRuleViolation;
use App\Business\Categorias\UseCases\ManageCategories;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/** CU21 Gestionar categorías (administrador). */
final class GestionarCategorias extends Component
{
    public string $nueva = '';

    public ?string $editandoId = null;

    public string $nombreEditado = '';

    protected ManageCategories $categorias;

    public function boot(ManageCategories $categorias): void
    {
        $this->categorias = $categorias;
    }

    public function crear(): void
    {
        $this->validate(['nueva' => ['required', 'string', 'min:3', 'max:100']], [], ['nueva' => 'nombre']);

        try {
            $this->categorias->create($this->nueva);
            $this->nueva = '';
            session()->flash('ok', 'Categoría creada.');
        } catch (CategoryRuleViolation $e) {
            $this->addError('nueva', $e->getMessage());
        }
    }

    public function editar(string $id, string $nombre): void
    {
        $this->resetValidation();
        $this->editandoId = $id;
        $this->nombreEditado = $nombre;
    }

    public function cancelarEdicion(): void
    {
        $this->reset('editandoId', 'nombreEditado');
        $this->resetValidation();
    }

    public function guardarEdicion(): void
    {
        $this->validate(['nombreEditado' => ['required', 'string', 'min:3', 'max:100']], [], ['nombreEditado' => 'nombre']);

        try {
            $this->categorias->rename((string) $this->editandoId, $this->nombreEditado);
            $this->cancelarEdicion();
            session()->flash('ok', 'Categoría actualizada.');
        } catch (CategoryRuleViolation $e) {
            $this->addError('nombreEditado', $e->getMessage());
        }
    }

    public function eliminar(string $id): void
    {
        try {
            $this->categorias->delete($id);
            session()->flash('ok', 'Categoría eliminada.');
        } catch (CategoryRuleViolation $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.administracion.gestionar-categorias', [
            'categorias' => $this->categorias->list(),
        ])->layout('layouts.admin')->title('Categorías');
    }
}
