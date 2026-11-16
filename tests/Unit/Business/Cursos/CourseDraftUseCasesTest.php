<?php

namespace Tests\Unit\Business\Cursos;

use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\DTO\CourseDetails;
use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseDraftPatch;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\DTO\CreateCourseDraftInput;
use App\Business\Cursos\DTO\FieldChange;
use App\Business\Cursos\DTO\InvalidCourseDraftData;
use App\Business\Cursos\DTO\ListTutorCoursesInput;
use App\Business\Cursos\DTO\TutorCourseDetailsInput;
use App\Business\Cursos\DTO\UpdateCourseDraftInput;
use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Cursos\Policies\CourseDraftRules;
use App\Business\Cursos\Policies\CourseOwnershipPolicy;
use App\Business\Cursos\UseCases\CourseNotDraft;
use App\Business\Cursos\UseCases\CourseNotFound;
use App\Business\Cursos\UseCases\CourseNotOwned;
use App\Business\Cursos\UseCases\CreateCourseDraft;
use App\Business\Cursos\UseCases\GetTutorCourseDetails;
use App\Business\Cursos\UseCases\ListTutorCourses;
use App\Business\Cursos\UseCases\TutorNotEnabled;
use App\Business\Cursos\UseCases\UpdateCourseDraft;
use App\Business\Cursos\UseCases\AcademicProfileIncomplete;
use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\Business\Usuarios\DTO\AcademicProfileData;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

final class CourseDraftUseCasesTest extends TestCase
{
    public function test_enabled_tutor_creates_draft_inside_transaction_and_commits(): void
    {
        $courses = new FakeCourseRepository();
        $courses->created = $this->course('course-1', 'tutor-1');
        $transactions = new FakeTransactionManager($courses);

        $result = $this->createUseCase($courses, $transactions)->execute(
            new CreateCourseDraftInput('tutor-1', new CourseDraftData(title: 'Álgebra')),
        );

        self::assertSame(CourseStatus::BORRADOR, $result->status);
        self::assertSame('tutor-1', $result->ownerTutorId);
        self::assertTrue($courses->createRanInTransaction);
        self::assertSame(1, $transactions->commitCount);
        self::assertSame(0, $transactions->rollbackCount);
    }

    public function test_disabled_tutor_cannot_create_a_draft(): void
    {
        $courses = new FakeCourseRepository();
        $transactions = new FakeTransactionManager($courses);
        $this->expectException(TutorNotEnabled::class);

        $this->createUseCase($courses, $transactions, enabled: false)
            ->execute(new CreateCourseDraftInput('tutor-1', new CourseDraftData()));
    }

    public function test_tutor_without_complete_academic_profile_cannot_create_a_draft(): void
    {
        $courses = new FakeCourseRepository();
        $transactions = new FakeTransactionManager($courses);
        $this->expectException(AcademicProfileIncomplete::class);

        try {
            $this->createUseCase($courses, $transactions, profileComplete: false)
                ->execute(new CreateCourseDraftInput('tutor-1', new CourseDraftData(title: 'Álgebra')));
        } finally {
            self::assertSame(0, $transactions->commitCount);
        }
    }

    public function test_invalid_creation_result_rolls_back_and_propagates_exception(): void
    {
        $courses = new FakeCourseRepository();
        $courses->created = $this->course('course-1', 'tutor-1', CourseStatus::PUBLICADO);
        $transactions = new FakeTransactionManager($courses);

        try {
            $this->createUseCase($courses, $transactions)->execute(
                new CreateCourseDraftInput('tutor-1', new CourseDraftData()),
            );
            self::fail('The invalid repository result should throw.');
        } catch (LogicException $exception) {
            self::assertStringContainsString('BORRADOR', $exception->getMessage());
        }

        self::assertNull($courses->persistedCourse);
        self::assertSame(0, $transactions->commitCount);
        self::assertSame(1, $transactions->rollbackCount);
    }

    public function test_repository_exception_during_creation_rolls_back_and_propagates(): void
    {
        $courses = new FakeCourseRepository();
        $courses->created = $this->course('course-1', 'tutor-1');
        $courses->throwAfterCreate = true;
        $transactions = new FakeTransactionManager($courses);

        try {
            $this->createUseCase($courses, $transactions)->execute(
                new CreateCourseDraftInput('tutor-1', new CourseDraftData()),
            );
            self::fail('The repository exception should propagate.');
        } catch (RuntimeException $exception) {
            self::assertSame('Simulated repository failure.', $exception->getMessage());
        }

        self::assertNull($courses->persistedCourse);
        self::assertSame(1, $transactions->rollbackCount);
    }

    public function test_creation_validates_positive_seats_before_opening_transaction(): void
    {
        $courses = new FakeCourseRepository();
        $transactions = new FakeTransactionManager($courses);
        $this->expectException(InvalidCourseDraftData::class);

        $this->createUseCase($courses, $transactions)->execute(
            new CreateCourseDraftInput('tutor-1', new CourseDraftData(totalSeats: 0)),
        );
    }

    public function test_tutor_course_list_filters_foreign_results_in_business_logic(): void
    {
        $courses = new FakeCourseRepository();
        $courses->listedForTutor = [
            $this->summary('course-1', 'tutor-1'),
            $this->summary('course-2', 'different-tutor'),
        ];

        $result = (new ListTutorCourses($courses))->execute(new ListTutorCoursesInput('tutor-1'));

        self::assertCount(1, $result);
        self::assertSame('course-1', $result[0]->id);
    }

    public function test_tutor_can_read_own_course_details(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1');

        $result = (new GetTutorCourseDetails($courses, new CourseOwnershipPolicy()))
            ->execute(new TutorCourseDetailsInput('tutor-1', 'course-1'));

        self::assertSame('course-1', $result->id);
    }

    public function test_tutor_cannot_read_another_tutors_course(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'different-tutor');
        $this->expectException(CourseNotOwned::class);

        (new GetTutorCourseDetails($courses, new CourseOwnershipPolicy()))
            ->execute(new TutorCourseDetailsInput('tutor-1', 'course-1'));
    }

    public function test_missing_course_is_rejected(): void
    {
        $this->expectException(CourseNotFound::class);

        (new GetTutorCourseDetails(new FakeCourseRepository(), new CourseOwnershipPolicy()))
            ->execute(new TutorCourseDetailsInput('tutor-1', 'missing'));
    }

    public function test_enabled_owner_updates_draft_inside_transaction_and_commits(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1');
        $courses->updated = $this->course('course-1', 'tutor-1', title: 'Nuevo título');
        $transactions = new FakeTransactionManager($courses);

        $result = $this->updateUseCase($courses, $transactions)->execute(new UpdateCourseDraftInput(
            'tutor-1',
            'course-1',
            new CourseDraftPatch(title: new FieldChange('Nuevo título')),
        ));

        self::assertSame('Nuevo título', $result->title);
        self::assertTrue($courses->eligibilityCheckRanInTransaction);
        self::assertTrue($courses->findRanInTransaction);
        self::assertTrue($courses->updateRanInTransaction);
        self::assertSame(1, $transactions->commitCount);
        self::assertSame(0, $transactions->rollbackCount);
    }

    public function test_explicit_null_remains_distinct_from_omitted_field(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1');
        $transactions = new FakeTransactionManager($courses);

        $this->updateUseCase($courses, $transactions)->execute(new UpdateCourseDraftInput(
            'tutor-1',
            'course-1',
            new CourseDraftPatch(description: new FieldChange(null)),
        ));

        self::assertNull($courses->updatedPatch?->title);
        self::assertNotNull($courses->updatedPatch?->description);
        self::assertNull($courses->updatedPatch?->description?->value);
    }

    public function test_empty_string_is_preserved_as_a_sent_value(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1');
        $transactions = new FakeTransactionManager($courses);

        $this->updateUseCase($courses, $transactions)->execute(new UpdateCourseDraftInput(
            'tutor-1',
            'course-1',
            new CourseDraftPatch(title: new FieldChange('')),
        ));

        self::assertSame('', $courses->updatedPatch?->title?->value);
    }

    public function test_disabled_tutor_cannot_update_a_draft(): void
    {
        $courses = new FakeCourseRepository();
        $transactions = new FakeTransactionManager($courses);

        try {
            $this->updateUseCase($courses, $transactions, enabled: false)->execute(
                new UpdateCourseDraftInput('tutor-1', 'course-1', new CourseDraftPatch()),
            );
            self::fail('A disabled tutor must be rejected.');
        } catch (TutorNotEnabled) {
            self::assertTrue($courses->eligibilityCheckRanInTransaction);
            self::assertSame(1, $transactions->rollbackCount);
            self::assertFalse($courses->findRanInTransaction);
        }
    }

    public function test_tutor_cannot_update_another_tutors_draft(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'different-tutor');
        $transactions = new FakeTransactionManager($courses);
        $this->expectException(CourseNotOwned::class);

        $this->updateUseCase($courses, $transactions)->execute(
            new UpdateCourseDraftInput('tutor-1', 'course-1', new CourseDraftPatch()),
        );
    }

    public function test_published_course_cannot_be_updated_as_a_draft(): void
    {
        $this->assertCourseStateCannotBeUpdated(CourseStatus::PUBLICADO);
    }

    public function test_cancelled_course_cannot_be_updated_as_a_draft(): void
    {
        $this->assertCourseStateCannotBeUpdated(CourseStatus::CANCELADO);
    }

    public function test_finalized_course_cannot_be_updated_as_a_draft(): void
    {
        $this->assertCourseStateCannotBeUpdated(CourseStatus::FINALIZADO);
    }

    public function test_repository_conditional_write_rejection_rolls_back(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1');
        $courses->rejectConditionalUpdate = true;
        $transactions = new FakeTransactionManager($courses);
        $this->expectException(CourseNotDraft::class);

        $this->updateUseCase($courses, $transactions)->execute(
            new UpdateCourseDraftInput('tutor-1', 'course-1', new CourseDraftPatch()),
        );
    }

    public function test_bad_update_result_rolls_back_owner_and_status_changes(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1');
        $courses->updated = $this->course('course-1', 'other-tutor', CourseStatus::PUBLICADO);
        $transactions = new FakeTransactionManager($courses);

        try {
            $this->updateUseCase($courses, $transactions)->execute(
                new UpdateCourseDraftInput('tutor-1', 'course-1', new CourseDraftPatch()),
            );
            self::fail('The invalid updated row should throw.');
        } catch (LogicException) {
            // Expected: the transaction manager restores the previous draft.
        }

        self::assertSame(CourseStatus::BORRADOR, $courses->currentCourse()?->status);
        self::assertSame('tutor-1', $courses->currentCourse()?->ownerTutorId);
        self::assertSame(1, $transactions->rollbackCount);
    }

    public function test_repository_exception_after_update_rolls_back_and_propagates(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1');
        $courses->updated = $this->course('course-1', 'other-tutor', CourseStatus::PUBLICADO);
        $courses->throwAfterUpdate = true;
        $transactions = new FakeTransactionManager($courses);

        try {
            $this->updateUseCase($courses, $transactions)->execute(
                new UpdateCourseDraftInput('tutor-1', 'course-1', new CourseDraftPatch()),
            );
            self::fail('The repository exception should propagate.');
        } catch (RuntimeException $exception) {
            self::assertSame('Simulated repository failure.', $exception->getMessage());
        }

        self::assertSame(CourseStatus::BORRADOR, $courses->currentCourse()?->status);
        self::assertSame(1, $transactions->rollbackCount);
    }

    public function test_invalid_patch_is_rejected_inside_transaction(): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1');
        $transactions = new FakeTransactionManager($courses);

        try {
            $this->updateUseCase($courses, $transactions)->execute(new UpdateCourseDraftInput(
                'tutor-1',
                'course-1',
                new CourseDraftPatch(totalSeats: new FieldChange(0)),
            ));
            self::fail('Invalid seats must be rejected.');
        } catch (InvalidCourseDraftData) {
            self::assertTrue($courses->eligibilityCheckRanInTransaction);
            self::assertSame(1, $transactions->rollbackCount);
            self::assertFalse($courses->findRanInTransaction);
            self::assertFalse($courses->updateRanInTransaction);
        }
    }

    private function assertCourseStateCannotBeUpdated(CourseStatus $status): void
    {
        $courses = new FakeCourseRepository();
        $courses->found = $this->course('course-1', 'tutor-1', $status);
        $transactions = new FakeTransactionManager($courses);
        $this->expectException(CourseNotDraft::class);

        $this->updateUseCase($courses, $transactions)->execute(
            new UpdateCourseDraftInput('tutor-1', 'course-1', new CourseDraftPatch()),
        );
    }

    private function createUseCase(
        FakeCourseRepository $courses,
        FakeTransactionManager $transactions,
        bool $enabled = true,
        bool $profileComplete = true,
    ): CreateCourseDraft {
        return new CreateCourseDraft(
            new FakeTutorEligibility($enabled),
            $courses,
            new CourseDraftRules(),
            $transactions,
            new FakeAcademicProfiles($profileComplete),
        );
    }

    private function updateUseCase(
        FakeCourseRepository $courses,
        FakeTransactionManager $transactions,
        bool $enabled = true,
    ): UpdateCourseDraft {
        return new UpdateCourseDraft(
            new FakeTutorEligibility($enabled, $courses),
            $courses,
            new CourseOwnershipPolicy(),
            new CourseDraftRules(),
            $transactions,
        );
    }

    private function course(
        string $id,
        string $owner,
        CourseStatus $status = CourseStatus::BORRADOR,
        ?string $title = null,
    ): CourseDetails {
        return new CourseDetails($id, $owner, $status, $title, null, null, null, null);
    }

    private function summary(string $id, string $owner): \App\Business\Cursos\DTO\CourseSummary
    {
        return new \App\Business\Cursos\DTO\CourseSummary(
            $id,
            $owner,
            CourseStatus::BORRADOR,
            null,
            null,
        );
    }
}

final class FakeTutorEligibility implements TutorEligibility
{
    public function __construct(
        private readonly bool $enabled,
        private readonly ?FakeCourseRepository $courses = null,
    )
    {
    }

    public function isEnabledTutor(string $tutorId): bool
    {
        if ($this->courses !== null) {
            $this->courses->eligibilityCheckRanInTransaction = $this->courses->transactionActive;
        }

        return $this->enabled;
    }
}

final class FakeTransactionManager implements TransactionManager
{
    public int $commitCount = 0;
    public int $rollbackCount = 0;

    public function __construct(private readonly FakeCourseRepository $courses)
    {
    }

    public function run(callable $operation): mixed
    {
        $previous = $this->courses->persistedCourse;
        $this->courses->transactionActive = true;

        try {
            $result = $operation();
            $this->commitCount++;

            return $result;
        } catch (Throwable $exception) {
            $this->courses->persistedCourse = $previous;
            $this->rollbackCount++;

            throw $exception;
        } finally {
            $this->courses->transactionActive = false;
        }
    }
}

final class FakeCourseRepository implements CourseRepository
{
    public ?CourseDetails $created = null;
    public ?CourseDetails $found = null;
    public ?CourseDetails $updated = null;
    public ?CourseDetails $persistedCourse = null;
    public ?string $createdOwnerId = null;
    public ?CourseDraftData $createdData = null;
    public ?string $listedOwnerId = null;
    public ?string $updatedOwnerId = null;
    public ?CourseDraftPatch $updatedPatch = null;
    public bool $transactionActive = false;
    public bool $createRanInTransaction = false;
    public bool $findRanInTransaction = false;
    public bool $updateRanInTransaction = false;
    public bool $eligibilityCheckRanInTransaction = false;
    public bool $rejectConditionalUpdate = false;
    public bool $throwAfterCreate = false;
    public bool $throwAfterUpdate = false;
    /** @var list<\App\Business\Cursos\DTO\CourseSummary> */
    public array $listedForTutor = [];

    public function createDraft(string $ownerTutorId, CourseDraftData $data): CourseDetails
    {
        $this->createRanInTransaction = $this->transactionActive;
        $this->createdOwnerId = $ownerTutorId;
        $this->createdData = $data;
        $this->persistedCourse = $this->created
            ?? throw new RuntimeException('No created course configured.');

        if ($this->throwAfterCreate) {
            throw new RuntimeException('Simulated repository failure.');
        }

        return $this->persistedCourse;
    }

    public function listByOwner(string $ownerTutorId): array
    {
        $this->listedOwnerId = $ownerTutorId;

        return $this->listedForTutor;
    }

    public function findById(string $courseId): ?CourseDetails
    {
        $this->findRanInTransaction = $this->transactionActive;

        return $this->currentCourse();
    }

    public function updateDraftOwnedBy(
        string $courseId,
        string $ownerTutorId,
        CourseDraftPatch $patch,
    ): ?CourseDetails {
        $this->updateRanInTransaction = $this->transactionActive;
        $current = $this->currentCourse();

        if (
            $this->rejectConditionalUpdate
            || $current === null
            || $current->ownerTutorId !== $ownerTutorId
            || $current->status !== CourseStatus::BORRADOR
        ) {
            return null;
        }

        $this->updatedOwnerId = $ownerTutorId;
        $this->updatedPatch = $patch;
        $this->persistedCourse = $this->updated ?? $current;

        if ($this->throwAfterUpdate) {
            throw new RuntimeException('Simulated repository failure.');
        }

        return $this->persistedCourse;
    }
    public function publishDraftOwnedBy(
    string $courseId,
    string $ownerTutorId,
): ?CourseDetails {
    return null;
}

/**@return list<\App\Business\Cursos\DTO\PublishedCourseSummary>*/
public function searchPublished(
    \App\Business\Cursos\DTO\CourseCatalogFilters $filters,
): array {
    return [];
}

    public function currentCourse(): ?CourseDetails
    {
        return $this->persistedCourse ?? $this->found;
    }
}

final class FakeAcademicProfiles implements AcademicProfileRepository
{
    public function __construct(private readonly bool $complete)
    {
    }

    public function ensureForTutor(string $tutorId): void
    {
    }

    public function findForTutor(string $tutorId): ?AcademicProfileData
    {
        return $this->complete
            ? new AcademicProfileData('Bio', [], ['Álgebra'])
            : new AcademicProfileData(null, [], []);
    }

    public function saveForTutor(string $tutorId, AcademicProfileData $profile): void
    {
    }
}
