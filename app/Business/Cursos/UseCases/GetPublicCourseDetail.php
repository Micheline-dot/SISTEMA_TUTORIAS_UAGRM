<?php

namespace App\Business\Cursos\UseCases;

use App\Business\Calificaciones\Contracts\RatingRepository;
use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\SyllabusRepository;
use App\Business\Cursos\Contracts\TutorRatingRepository;
use App\Business\Cursos\DTO\CourseStatus;
use App\Business\Cursos\DTO\PublicCourseDetail;
use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\Business\Usuarios\Contracts\UserProfileRepository;

/**
 * CU13 Visualizar detalles del curso: datos, temario y perfil del tutor.
 * Un curso PUBLICADO lo ve cualquier estudiante; uno cancelado o finalizado
 * solo quien estuvo inscrito o el propio tutor.
 */
final class GetPublicCourseDetail
{
    public function __construct(
        private readonly CourseRepository $courses,
        private readonly SyllabusRepository $syllabus,
        private readonly UserProfileRepository $users,
        private readonly AcademicProfileRepository $academicProfiles,
        private readonly TutorRatingRepository $ratings,
        private readonly InscripcionRepository $inscripciones,
        private readonly RatingRepository $reviews,
    ) {
    }

    public function execute(string $viewerId, string $courseId): PublicCourseDetail
    {
        $course = ctype_digit($courseId) ? $this->courses->findById($courseId) : null;

        if ($course === null || $course->status === CourseStatus::BORRADOR) {
            throw new CourseNotFound('Course was not found.');
        }

        $isOwner = $course->ownerTutorId === $viewerId;
        $isEnrolled = $this->inscripciones->buscarInscripcionActiva($viewerId, $courseId) !== null;
        $participated = $this->inscripciones->buscarInscripcionAnterior($viewerId, $courseId) !== null;

        if ($course->status !== CourseStatus::PUBLICADO && ! $isOwner && ! $isEnrolled && ! $participated) {
            throw new CourseNotFound('Course was not found.');
        }

        $tutor = $this->users->find($course->ownerTutorId);
        $profile = $this->academicProfiles->findForTutor($course->ownerTutorId);
        $rating = $this->ratings->summaryForTutor($course->ownerTutorId);

        return new PublicCourseDetail(
            course: $course,
            topics: $this->syllabus->topicsOf($courseId),
            tutorId: $course->ownerTutorId,
            tutorName: $tutor === null ? 'Tutor UAGRM' : trim($tutor->firstNames.' '.$tutor->lastNames),
            tutorFaculty: $tutor?->faculty,
            tutorHasPhoto: $tutor?->hasPhoto() ?? false,
            tutorBiography: $profile?->biography,
            tutorKnowledge: $profile?->knowledge ?? [],
            tutorLinks: $profile?->links ?? [],
            tutorRating: $rating['average'],
            tutorRatingCount: $rating['count'],
            viewerIsEnrolled: $isEnrolled,
            viewerIsOwner: $isOwner,
            viewerAlreadyParticipated: $participated,
            latestReviews: $this->reviews->latestReviewsForTutor($course->ownerTutorId, 5),
        );
    }
}
