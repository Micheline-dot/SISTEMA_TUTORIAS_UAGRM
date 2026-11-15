<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\DTO\UpdateCourseDraftInput;
use App\Business\Cursos\Policies\CourseDraftRules;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;

final class UpdateCourseDraft
{
    public function __construct(
        private readonly TutorEligibility $tutorEligibility,
        private readonly CourseRepository $courses,
        private readonly CourseOwnershipPolicy $ownership,
        private readonly CourseDraftRules $draftRules,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(UpdateCourseDraftInput $input): CourseDetails
    {
        return $this->transactions->run(function () use ($input): CourseDetails {
            if (! $this->tutorEligibility->isEnabledTutor($input->tutorId)) {
                throw new TutorNotEnabled('Only enabled tutors can edit course drafts.');
            }

            $this->draftRules->assertValidPatch($input->course);

            $course = $this->courses->findById($input->courseId);

            if ($course === null) {
                throw new CourseNotFound('Course was not found.');
            }

            $this->ownership->assertOwner($input->tutorId, $course);

            if ($course->status !== CourseStatus::BORRADOR) {
                throw new CourseNotDraft('Only course drafts can be edited by this operation.');
            }

            $updated = $this->courses->updateDraftOwnedBy(
                $input->courseId,
                $input->tutorId,
                $input->course,
            );

            if ($updated === null) {
                throw new CourseNotDraft('The course is no longer an editable draft owned by this tutor.');
            }

            if (
                $updated->ownerTutorId !== $input->tutorId
                || $updated->status !== CourseStatus::BORRADOR
            ) {
                throw new \LogicException(
                    'CourseRepository must preserve course ownership and BORRADOR status during draft updates.',
                );
            }

            return $updated;
        });
    }
}
