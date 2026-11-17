<?php

namespace App\Presentation\Livewire\Cursos;

use App\Business\Categorias\UseCases\ManageCategories;
use App\Business\Cursos\DTO\CourseCatalogFilters;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\UseCases\SearchPublishedCourses;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/** CU11 Realizar búsqueda · CU12 Filtrar resultados. */
final class CatalogoTutorias extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $categoria = '';

    #[Url(except: '')]
    public string $modality = '';

    #[Url(except: '')]
    public string $free = '';

    #[Url(except: '')]
    public string $valoracion = '';

    #[Url(except: false)]
    public bool $conCupos = false;

    #[Url(except: 'RECIENTES')]
    public string $orden = 'RECIENTES';

    protected SearchPublishedCourses $searchCourses;

    protected ManageCategories $categorias;

    public function boot(SearchPublishedCourses $searchCourses, ManageCategories $categorias): void
    {
        $this->searchCourses = $searchCourses;
        $this->categorias = $categorias;
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'categoria', 'modality', 'free', 'valoracion', 'conCupos', 'orden']);
    }

    public function render(): View
    {
        $filters = new CourseCatalogFilters(
            search: trim($this->search) !== '' ? mb_substr(trim($this->search), 0, 100) : null,
            modality: CourseModality::tryFrom($this->modality),
            isFree: match ($this->free) { '1' => true, '0' => false, default => null },
            categoryId: ctype_digit($this->categoria) ? $this->categoria : null,
            minRating: in_array($this->valoracion, ['3', '4', '5'], true) ? (int) $this->valoracion : null,
            onlyAvailable: $this->conCupos,
            sort: in_array($this->orden, ['RECIENTES', 'PROXIMOS', 'VALORACION'], true) ? $this->orden : 'RECIENTES',
        );

        $hayFiltros = $this->search !== '' || $this->categoria !== '' || $this->modality !== ''
            || $this->free !== '' || $this->valoracion !== '' || $this->conCupos;

        return view('livewire.cursos.catalogo-tutorias', [
            'tutorias' => $this->searchCourses->execute($filters),
            'categorias' => $this->categorias->list(),
            'hayFiltros' => $hayFiltros,
        ])->layout('layouts.student')->title('Buscar tutorías');
    }
}
