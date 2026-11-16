<?php

namespace App\Providers;

use App\Business\Inscripciones\Contracts\InscripcionRepository;
use App\Business\Inscripciones\Contracts\StudentEligibility;

use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Cursos\Contracts\CourseRepository;
use App\Business\Cursos\Contracts\TutorEligibility;
use App\Business\Usuarios\Contracts\AcademicProfileRepository;
use App\Business\Usuarios\Contracts\FacultyRepository;
use App\Business\Usuarios\Contracts\PasswordHasher;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\Contracts\SessionAuthenticator;
use App\Business\Usuarios\Contracts\UserRepository;
use App\DataAccess\PostgreSQL\Courses\Repositories\EloquentCourseRepository;
use App\DataAccess\PostgreSQL\Administracion\Repositories\EloquentAdminCourseRepository;
use App\DataAccess\PostgreSQL\Courses\Repositories\EloquentTutorEligibility;
use App\DataAccess\PostgreSQL\Transactions\LaravelTransactionManager;
use App\DataAccess\PostgreSQL\Users\Repositories\EloquentAcademicProfileRepository;
use App\DataAccess\PostgreSQL\Users\Repositories\EloquentFacultyRepository;
use App\DataAccess\PostgreSQL\Users\Repositories\EloquentRoleAssignmentRepository;
use App\DataAccess\PostgreSQL\Users\Repositories\EloquentUserRepository;
use App\DataAccess\PostgreSQL\Users\Security\LaravelPasswordHasher;
use App\DataAccess\PostgreSQL\Users\Security\LaravelSessionAuthenticator;
use App\DataAccess\PostgreSQL\Inscripciones\Repositories\EloquentInscripcionRepository;
use App\DataAccess\PostgreSQL\Inscripciones\Repositories\EloquentStudentEligibility;
use App\Business\Administracion\Contracts\AdminCourseRepository;
use Illuminate\Support\ServiceProvider;
use App\Business\Habilitaciones\Contracts\SolicitudHabilitacionRepository;
use App\DataAccess\PostgreSQL\Habilitaciones\Repositories\EloquentSolicitudHabilitacionRepository;

final class BusinessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
        $this->app->bind(CourseRepository::class, EloquentCourseRepository::class);
        $this->app->bind(AdminCourseRepository::class, EloquentAdminCourseRepository::class);
        $this->app->bind(TutorEligibility::class, EloquentTutorEligibility::class);

        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(AcademicProfileRepository::class, EloquentAcademicProfileRepository::class);
        $this->app->bind(FacultyRepository::class, EloquentFacultyRepository::class);
        $this->app->bind(RoleAssignmentRepository::class, EloquentRoleAssignmentRepository::class);
        $this->app->bind(PasswordHasher::class, LaravelPasswordHasher::class);
        $this->app->bind(SessionAuthenticator::class, LaravelSessionAuthenticator::class);
        $this->app->bind(
    SolicitudHabilitacionRepository::class,
    EloquentSolicitudHabilitacionRepository::class
);
        $this->app->bind(InscripcionRepository::class, EloquentInscripcionRepository::class);
        $this->app->bind(StudentEligibility::class, EloquentStudentEligibility::class);
    }
}