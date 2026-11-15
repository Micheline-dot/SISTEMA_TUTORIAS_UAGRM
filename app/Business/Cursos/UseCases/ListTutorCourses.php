<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\DTO\CourseSummary;
use App\Business\Cursos\DTO\ListTutorCoursesInput;

final class ListTutorCourses
{
    public function __construct(private readonly CourseRepository $courses)
    {
    }

    /** @return list<CourseSummary> */
    public function execute(ListTutorCoursesInput $input): array
    {
        return array_values(array_filter(
            $this->courses->listByOwner($input->tutorId),
            static fn (CourseSummary $course): bool => $course->ownerTutorId === $input->tutorId,
        ));
    }
}
