<?php

namespace App\Business\Usuarios\UseCases;

use App\Business\Administracion\UseCases\CancelAdminCourse;
use App\Business\Notificaciones\UseCases\EnviarNotificacion;
use App\Business\Shared\Contracts\TransactionManager;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;
use App\Business\Usuarios\Contracts\UserAdminRepository;
use App\Business\Usuarios\DTO\CreateAdministratorInput;
use App\Business\Usuarios\DTO\RegisterUserInput;
use App\Business\Usuarios\DTO\UserSummary;
use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\Exceptions\ProfileNotFound;
use App\Business\Usuarios\Exceptions\UserManagementRuleViolation;

/**
 * CU20 Gestionar usuarios (administrador): listar/buscar, crear, editar,
 * activar/desactivar y degradar a un tutor.
 */
final class ManageUsers
{
    public const PER_PAGE = 15;

    public function __construct(
        private readonly UserAdminRepository $users,
        private readonly RoleAssignmentRepository $roles,
        private readonly RegisterUser $registerUser,
        private readonly CreateAdministrator $createAdministrator,
        private readonly UpdatePersonalData $updatePersonalData,
        private readonly CancelAdminCourse $cancelCourse,
        private readonly EnviarNotificacion $notificar,
        private readonly TransactionManager $transactions,
    ) {
    }

    /** @return array{items: list<UserSummary>, total: int} */
    public function list(string $text = '', string $role = '', string $status = '', int $page = 1): array
    {
        $role = in_array($role, ['ESTUDIANTE', 'TUTOR', 'ADMINISTRADOR'], true) ? $role : '';
        $status = in_array($status, ['ACTIVO', 'INACTIVO'], true) ? $status : '';

        return $this->users->search(trim($text), $role, $status, max(1, $page), self::PER_PAGE);
    }

    public function find(string $userId): UserSummary
    {
        return $this->users->find($userId)
            ?? throw new ProfileNotFound('El usuario no existe.');
    }

    /** Crea una cuenta de estudiante (correo @gmail.com) o de administrador. */
    public function create(
        string $role,
        string $firstNames,
        string $lastNames,
        string $email,
        string $password,
        ?string $phone = null,
        ?string $position = null,
    ): string {
        return match ($role) {
            'ESTUDIANTE' => $this->registerUser->execute(
                new RegisterUserInput($firstNames, $lastNames, $email, $password)
            ),
            'ADMINISTRADOR' => $this->createAdministrator->execute(new CreateAdministratorInput(
                $firstNames, $lastNames, RegisterUser::normalizeEmail($email), $password,
                (string) $phone, (string) $position,
            )),
            default => throw new UserManagementRuleViolation('Solo se pueden crear cuentas de estudiante o de administrador. El rol de tutor se obtiene con una solicitud aprobada.'),
        };
    }

    /** Edita nombres, apellidos y teléfono (el correo no se modifica). */
    public function update(string $userId, string $firstNames, string $lastNames, ?string $phone): void
    {
        $this->find($userId);
        $this->updatePersonalData->execute($userId, $firstNames, $lastNames, $phone);
    }

    public function setActive(string $adminId, string $userId, bool $active): void
    {
        $user = $this->find($userId);

        if (! $active && $userId === $adminId) {
            throw new UserManagementRuleViolation('No puedes desactivar tu propia cuenta.');
        }

        if (! $active && $user->hasRole('ADMINISTRADOR') && $user->active
            && $this->users->countActiveAdministrators() <= 1) {
            throw new UserManagementRuleViolation('No puedes desactivar al único administrador activo.');
        }

        if ($user->active === $active) {
            return;
        }

        $this->users->setActive($userId, $active);
    }

    /**
     * Quita el rol de tutor (conserva su historial). Sus tutorías publicadas
     * se cancelan con el motivo, y se avisa al tutor y a los inscritos.
     *
     * @return int cantidad de tutorías canceladas
     */
    public function demoteTutor(string $userId, string $reason): int
    {
        $reason = trim($reason);

        if (mb_strlen($reason) < 5) {
            throw new InvalidProfileData('Indica el motivo (al menos 5 caracteres).');
        }

        $user = $this->find($userId);

        if (! $user->hasRole('TUTOR')) {
            throw new UserManagementRuleViolation('El usuario no es tutor.');
        }

        return $this->transactions->run(function () use ($userId, $reason): int {
            if (! $this->roles->revokeTutor($userId)) {
                throw new UserManagementRuleViolation('El usuario ya no es tutor.');
            }

            $courses = $this->users->publishedCourseIdsOfTutor($userId);

            foreach ($courses as $courseId) {
                $this->cancelCourse->execute($courseId, 'Tutor deshabilitado por la administración: '.$reason);
            }

            $this->notificar->aUsuario(
                $userId,
                'Tu rol de tutor fue retirado',
                'La administración retiró tu habilitación como tutor. Motivo: '.$reason
                    .($courses === [] ? '' : ' Tus tutorías publicadas fueron canceladas.'),
                '/solicitar-habilitacion',
            );

            return count($courses);
        });
    }
}
