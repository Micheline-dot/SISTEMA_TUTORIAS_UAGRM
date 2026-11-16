<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;
use App\Business\Shared\Contracts\TransactionManager;

final class PublishCourseDraft
{
    public function __construct(
        private readonly TutorEligibility $tutorEligibility,
        private readonly CourseRepository $courses,
        private readonly CourseOwnershipPolicy $ownership,
        private readonly TransactionManager $transactions,
    ) {
    }

    public function execute(
        string $courseId,
        string $tutorId,
    ): CourseDetails {
        if (! $this->tutorEligibility->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled(
                'Only enabled tutors can publish courses.'
            );
        }

        return $this->transactions->run(function () use (
            $courseId,
            $tutorId
        ): CourseDetails {
            $course = $this->courses->findById($courseId);

            if ($course === null) {
                throw new CourseNotFound('Course not found.');
            }

            $this->ownership->assertOwner($tutorId, $course);

            if ($course->status !== CourseStatus::BORRADOR) {
                throw new CourseNotDraft(
                    'Only draft courses can be published.'
                );
            }

            $published = $this->courses->publishDraftOwnedBy(
                $courseId,
                $tutorId,
            );

            if ($published === null) {
                throw new CourseNotDraft(
                    'The course could not be published.'
                );
            }

            if ($published->status !== CourseStatus::PUBLICADO) {
                throw new \LogicException(
                    'CourseRepository must publish courses in PUBLICADO status.'
                );
            }

            return $published;
        });
    }
}