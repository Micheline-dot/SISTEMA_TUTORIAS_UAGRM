<?php

namespace App\Business\Cursos\Contracts;

use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseSummary;
use App\Business\Cursos\DTO\CourseCatalogFilters;
use App\Business\Cursos\DTO\PublishedCourseSummary;

interface CourseRepository
{
    public function createDraft(
        string $ownerTutorId,
        CourseDraftData $data,
    ): CourseDetails;

    /** @return list<CourseSummary> */
    public function listByOwner(string $ownerTutorId): array;

    public function findById(string $courseId): ?CourseDetails;

    public function updateDraftOwnedBy(
        string $courseId,
        string $ownerTutorId,
        CourseDraftPatch $patch,
    ): ?CourseDetails;

    public function publishDraftOwnedBy(
        string $courseId,
        string $ownerTutorId,
    ): ?CourseDetails;

    /** @return list<PublishedCourseSummary> */
    public function searchPublished(
        CourseCatalogFilters $filters,
    ): array;
}