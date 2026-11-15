<?php

namespace App\Business\Cursos\Policies;

use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\UseCases\CourseNotOwned;

final class CourseOwnershipPolicy
{
    public function assertOwner(string $tutorId, CourseDetails $course): void
    {
        if ($course->ownerTutorId !== $tutorId) {
            throw new CourseNotOwned('The tutor cannot manage a course owned by another tutor.');
        }
    }
}
