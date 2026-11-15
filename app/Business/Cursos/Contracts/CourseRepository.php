<?php

namespace App\Business\Cursos\Contracts;

use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseSummary;

interface CourseRepository
{
    /**
     * Creates and persists the course with status BORRADOR.
     * The returned details must reflect the persisted row.
     * When called inside TransactionManager::run(), use that same configured
     * connection and do not commit an independent transaction.
     */
    public function createDraft(string $ownerTutorId, CourseDraftData $data): CourseDetails;

    /** @return list<CourseSummary> */
    public function listByOwner(string $ownerTutorId): array;

    public function findById(string $courseId): ?CourseDetails;

    /**
     * Recheck ownership and BORRADOR status as part of the write, not only in a
     * preceding read. Return details reflecting the persisted row.
     * Null patch properties are omitted; FieldChange(null) is an explicit null.
     * Return null if the ownership or state condition no longer holds.
     * When called inside TransactionManager::run(), use that same configured
     * connection and do not commit an independent transaction.
     */
    public function updateDraftOwnedBy(
        string $courseId,
        string $ownerTutorId,
        CourseDraftPatch $patch,
    ): ?CourseDetails;
}
