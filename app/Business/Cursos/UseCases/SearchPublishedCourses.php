<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\DTO\CourseCatalogFilters;
use App\Business\Cursos\DTO\PublishedCourseSummary;

final class SearchPublishedCourses
{
    public function __construct(
        private readonly CourseRepository $courses,
    ) {
    }

    /** @return list<PublishedCourseSummary> */
    public function execute(
        CourseCatalogFilters $filters,
    ): array {
        return $this->courses->searchPublished($filters);
    }
}