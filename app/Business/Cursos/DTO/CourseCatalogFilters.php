<?php

namespace App\Business\Cursos\DTO;

/**
 * CU11/CU12: búsqueda y filtros del catálogo.
 * sort: RECIENTES | PROXIMOS | VALORACION
 */
final readonly class CourseCatalogFilters
{
    public function __construct(
        public ?string $search = null,
        public ?CourseModality $modality = null,
        public ?bool $isFree = null,
        public ?string $categoryId = null,
        public ?int $minRating = null,
        public bool $onlyAvailable = false,
        public string $sort = 'RECIENTES',
        /** Solo tutorías de este tutor (perfil público del tutor). */
        public ?string $tutorId = null,
    ) {
    }
}
