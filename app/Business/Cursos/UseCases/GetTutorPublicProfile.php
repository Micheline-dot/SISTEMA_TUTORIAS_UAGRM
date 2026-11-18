<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Calificaciones\Contracts\RatingRepository;
use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Cursos\Contracts\TutorRatingRepository;
use App\Business\Cursos\DTO\CourseCatalogFilters;
use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\Business\Usuarios\Contracts\UserProfileRepository;

/**
 * (+) Perfil público del tutor (complementa CU13): datos académicos,
 * valoración, reseñas y sus tutorías publicadas. No expone teléfono ni correo;
 * el contacto se habilita en el detalle de la tutoría al inscribirse.
 */
final class GetTutorPublicProfile
{
    public function __construct(
        private readonly TutorEligibility $tutors,
        private readonly UserProfileRepository $users,
        private readonly AcademicProfileRepository $academicProfiles,
        private readonly TutorRatingRepository $ratings,
        private readonly RatingRepository $reviews,
        private readonly CourseRepository $courses,
    ) {
    }

    /**
     * @return array{
     *   id: string, nombre: string, facultad: ?string, tipo: string, tiene_foto: bool,
     *   biografia: ?string, conocimientos: list<string>, enlaces: list<string>,
     *   valoracion: ?float, calificaciones: int,
     *   resenas: list<\App\Business\Calificaciones\DTO\Review>,
     *   tutorias: list<\App\Business\Cursos\DTO\PublishedCourseSummary>
     * }
     */
    public function execute(string $tutorId): array
    {
        if (! ctype_digit($tutorId) || ! $this->tutors->isEnabledTutor($tutorId)) {
            throw new TutorNotEnabled('Tutor not found.');
        }

        $user = $this->users->find($tutorId) ?? throw new TutorNotEnabled('Tutor not found.');
        $profile = $this->academicProfiles->findForTutor($tutorId);
        $rating = $this->ratings->summaryForTutor($tutorId);

        return [
            'id' => $tutorId,
            'nombre' => trim($user->firstNames.' '.$user->lastNames),
            'facultad' => $user->faculty,
            'tipo' => $user->registrationType,
            'tiene_foto' => $user->hasPhoto(),
            'biografia' => $profile?->biography,
            'conocimientos' => $profile?->knowledge ?? [],
            'enlaces' => $profile?->links ?? [],
            'valoracion' => $rating['average'],
            'calificaciones' => $rating['count'],
            'resenas' => $this->reviews->latestReviewsForTutor($tutorId, 10),
            'tutorias' => $this->courses->searchPublished(new CourseCatalogFilters(sort: 'PROXIMOS', tutorId: $tutorId)),
        ];
    }
}
