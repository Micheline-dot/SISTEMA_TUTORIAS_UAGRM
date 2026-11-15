<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CreateCourseDraftInput;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\Policies\CourseDraftRules;
use LogicException;

final class CreateCourseDraft
{
    public function __construct(
        private readonly TutorEligibility $tutorEligibility,
        private readonly CourseRepository $courses,
        private readonly CourseDraftRules $draftRules,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(CreateCourseDraftInput $input): CourseDetails
    {
        if (! $this->tutorEligibility->isEnabledTutor($input->tutorId)) {
            throw new TutorNotEnabled('Only enabled tutors can create course drafts.');
        }

        $this->draftRules->assertValidNewDraft($input->course);

        return $this->transactions->run(function () use ($input): CourseDetails {
            $course = $this->courses->createDraft($input->tutorId, $input->course);

            if ($course->status !== CourseStatus::BORRADOR) {
                throw new LogicException('CourseRepository must create courses in BORRADOR status.');
            }

            return $course;
        });
    }
}
