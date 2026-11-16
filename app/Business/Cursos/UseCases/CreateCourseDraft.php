<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CreateCourseDraftInput;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\Policies\CourseDraftRules;
use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use LogicException;

final class CreateCourseDraft
{
    public function __construct(
        private readonly TutorEligibility $tutorEligibility,
        private readonly CourseRepository $courses,
        private readonly CourseDraftRules $draftRules,
        private readonly TransactionManager $transactions,
        private readonly AcademicProfileRepository $academicProfiles,
    ) {
    }

    public function execute(CreateCourseDraftInput $input): CourseDetails
    {
        if (! $this->tutorEligibility->isEnabledTutor($input->tutorId)) {
            throw new TutorNotEnabled('Only enabled tutors can create course drafts.');
        }

        // CU07: requiere perfil académico completo (biografía + conocimientos, CU06).
        if (! ($this->academicProfiles->findForTutor($input->tutorId)?->isComplete() ?? false)) {
            throw new AcademicProfileIncomplete('Complete your academic profile before creating courses.');
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
