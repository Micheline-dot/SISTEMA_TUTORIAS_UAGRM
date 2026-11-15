<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\TutorCourseDetailsInput;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;

final class GetTutorCourseDetails
{
    public function __construct(
        private readonly CourseRepository $courses,
        private readonly CourseOwnershipPolicy $ownership,
    ) {
    }

    public function execute(TutorCourseDetailsInput $input): CourseDetails
    {
        $course = $this->courses->findById($input->courseId);

        if ($course === null) {
            throw new CourseNotFound('Course was not found.');
        }

        $this->ownership->assertOwner($input->tutorId, $course);

        return $course;
    }
}
