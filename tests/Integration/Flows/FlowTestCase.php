<?php

namespace Tests\Integration\Flows;

use App\Business\Cursos\DTO\CourseDraftData;
use App\Business\Cursos\DTO\CourseModality;
use App\Business\Cursos\DTO\CreateCourseDraftInput;
use App\Business\Cursos\UseCases\CreateCourseDraft;
use App\Business\Cursos\UseCases\ManageSyllabus;
use App\Business\Cursos\UseCases\PublishCourseDraft;
use App\Business\Habilitaciones\UseCases\AprobarSolicitudHabilitacion;
use App\Business\Habilitaciones\UseCases\RegistrarSolicitudHabilitacion;
use App\Business\Inscripciones\UseCases\ConfirmParticipation;
use App\Business\Inscripciones\UseCases\VerifyAttendance;
use App\Business\Usuarios\DTO\CompleteAcademicDataInput;
use App\Business\Usuarios\DTO\CreateAdministratorInput;
use App\Business\Usuarios\DTO\RegisterUserInput;
use App\Business\Usuarios\UseCases\CompleteAcademicData;
use App\Business\Usuarios\UseCases\CreateInitialAdministrator;
use App\Business\Usuarios\UseCases\ManageAcademicProfile;
use App\Business\Usuarios\UseCases\RegisterUser;
use App\DataAccess\PostgreSQL\Users\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Integration\TestCase;

/**
 * Pruebas funcionales de flujo completo: datos creados con los casos de uso
 * reales (no con inserts directos) para que cada prueba recorra las 3 capas.
 */
abstract class FlowTestCase extends TestCase
{
    use RefreshDatabase;

    /** Contraseña de prueba: solo existe en esta base aislada. */
    protected const PASSWORD = 'ClavePrueba123';

    protected string $adminId;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->seed(CategorySeeder::class);
        $this->adminId = (string) app(CreateInitialAdministrator::class)->execute(new CreateAdministratorInput(
            'Admin', 'Pruebas', 'admin.pruebas@gmail.com', self::PASSWORD, '70000000', 'Administrador',
        ));
    }

    protected function faculty(int $index = 0): string
    {
        return config('catalogos.facultades')[$index];
    }

    protected function categoryId(int $index = 0): string
    {
        return (string) DB::table('categoria')->orderBy('id_categoria')->offset($index)->value('id_categoria');
    }

    protected function date(int $days): string
    {
        return date('Y-m-d', strtotime("{$days} days"));
    }

    /** Registra una cuenta (CU01) sin datos académicos. */
    protected function register(string $firstNames = 'Est', ?string $lastNames = null): string
    {
        $n = ++$this->sequence;

        return app(RegisterUser::class)->execute(new RegisterUserInput(
            $firstNames, $lastNames ?? "Prueba{$n}", "usuario{$n}.prueba@gmail.com", self::PASSWORD,
        ));
    }

    /** Estudiante con datos académicos completos (CU02). */
    protected function student(int $faculty = 0, ?string $lastNames = null): string
    {
        $id = $this->register('Est', $lastNames);
        app(CompleteAcademicData::class)->execute(new CompleteAcademicDataInput(
            $id, $this->faculty($faculty), 'ESTUDIANTE', '2100000'.str_pad((string) $this->sequence, 3, '0', STR_PAD_LEFT), null, null,
        ));

        return $id;
    }

    /** Tutor habilitado (CU03 + CU05) con perfil académico completo (CU06). */
    protected function tutor(?string $lastNames = null): string
    {
        $id = $this->register('Tutor', $lastNames);
        app(CompleteAcademicData::class)->execute(new CompleteAcademicDataInput(
            $id, $this->faculty(), 'DOCENTE', null, null, 'DOC-'.$this->sequence,
        ));
        $request = app(RegistrarSolicitudHabilitacion::class)->ejecutar((int) $id, ['curriculum' => 'solicitudes/cv.pdf']);
        app(AprobarSolicitudHabilitacion::class)->ejecutar($request, (int) $this->adminId);
        app(ManageAcademicProfile::class)->save($id, 'Docente con experiencia.', [], ['Matemáticas']);

        return $id;
    }

    /** Tutoría publicada (CU07 + CU08 + CU09). */
    protected function publishedCourse(string $tutorId, string $title = 'Cálculo I', int $seats = 10, int $category = 0, int $startInDays = 1): string
    {
        $draft = app(CreateCourseDraft::class)->execute(new CreateCourseDraftInput($tutorId, new CourseDraftData(
            $title, 'Descripción de la tutoría', CourseModality::VIRTUAL, $seats, true,
            $this->categoryId($category), $this->date($startInDays), $this->date(30),
        )));
        app(ManageSyllabus::class)->addTopic($tutorId, $draft->id, 'Introducción', null);
        app(PublishCourseDraft::class)->execute($draft->id, $tutorId);

        return $draft->id;
    }

    /** El tutor registra la asistencia y el estudiante la confirma (CU14). */
    protected function participate(string $tutorId, string $studentId, string $courseId): void
    {
        app(VerifyAttendance::class)->execute($tutorId, $courseId, $studentId);
        app(ConfirmParticipation::class)->execute($studentId, $courseId);
    }

    protected function user(string $id): User
    {
        return User::findOrFail((int) $id);
    }
}
