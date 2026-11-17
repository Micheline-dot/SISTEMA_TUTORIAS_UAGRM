<?php

namespace Tests\Integration\Flows;

use App\Business\Usuarios\DTO\CompleteAcademicDataInput;
use App\Business\Usuarios\DTO\RegisterUserInput;
use App\Business\Usuarios\Exceptions\AcademicDataAlreadyCompleted;
use App\Business\Usuarios\Exceptions\DuplicateAcademicCode;
use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\UseCases\CompleteAcademicData;
use App\Business\Usuarios\UseCases\GetUserProfile;
use App\Business\Usuarios\UseCases\InvalidRegistrationData;
use App\Business\Usuarios\UseCases\RegisterUser;
use App\Business\Usuarios\UseCases\RegistrationFailed;
use App\Business\Usuarios\UseCases\UpdatePersonalData;

/** CU01 Registrarse · CU02 Gestionar perfil. */
final class RegistroYPerfilFlowTest extends FlowTestCase
{
    public function test_registro_solo_acepta_gmail_y_contrasena_segura(): void
    {
        $register = app(RegisterUser::class);

        $this->expectException(InvalidRegistrationData::class);
        $register->execute(new RegisterUserInput('Ana', 'Pérez', 'ana@hotmail.com', self::PASSWORD));
    }

    public function test_registro_rechaza_contrasena_corta(): void
    {
        $this->expectException(InvalidRegistrationData::class);
        app(RegisterUser::class)->execute(new RegisterUserInput('Ana', 'Pérez', 'ana@gmail.com', '1234'));
    }

    public function test_registro_rechaza_correo_duplicado(): void
    {
        $register = app(RegisterUser::class);
        $register->execute(new RegisterUserInput('Ana', 'Pérez', 'ana@gmail.com', self::PASSWORD));

        $this->expectException(RegistrationFailed::class);
        $register->execute(new RegisterUserInput('Ana', 'Otra', 'ANA@gmail.com', self::PASSWORD));
    }

    public function test_registro_crea_estudiante_y_puede_iniciar_sesion(): void
    {
        $id = app(RegisterUser::class)->execute(new RegisterUserInput('Ana', 'Pérez', 'ana@gmail.com', self::PASSWORD));

        $this->assertDatabaseHas('estudiante', ['id_estudiante' => (int) $id]);
        $this->assertSame(['ESTUDIANTE'], $this->user($id)->nombresRoles());
        $this->assertTrue(auth()->attempt(['correo' => 'ana@gmail.com', 'password' => self::PASSWORD]));
    }

    public function test_datos_academicos_se_registran_una_sola_vez(): void
    {
        $id = $this->register();
        $complete = app(CompleteAcademicData::class);
        $complete->execute(new CompleteAcademicDataInput($id, $this->faculty(), 'AUXILIAR', '219000001', 'AUX-1', null));

        $profile = app(GetUserProfile::class)->execute($id);
        $this->assertSame('AUXILIAR', $profile->registrationType);
        $this->assertSame('AUX-1', $profile->assistantRegistry);

        $this->expectException(AcademicDataAlreadyCompleted::class);
        $complete->execute(new CompleteAcademicDataInput($id, $this->faculty(1), 'ESTUDIANTE', '219000002', null, null));
    }

    public function test_codigo_docente_no_se_repite(): void
    {
        $complete = app(CompleteAcademicData::class);
        $complete->execute(new CompleteAcademicDataInput($this->register(), $this->faculty(), 'DOCENTE', null, null, 'DOC-9'));

        $this->expectException(DuplicateAcademicCode::class);
        $complete->execute(new CompleteAcademicDataInput($this->register(), $this->faculty(), 'DOCENTE', null, null, 'DOC-9'));
    }

    public function test_datos_personales_se_actualizan_pero_el_correo_no(): void
    {
        $id = $this->register();
        $email = app(GetUserProfile::class)->execute($id)->email;

        app(UpdatePersonalData::class)->execute($id, 'Ana María', 'Pérez Rojas', '71234567');

        $profile = app(GetUserProfile::class)->execute($id);
        $this->assertSame('Ana María', $profile->firstNames);
        $this->assertSame('71234567', $profile->phone);
        $this->assertSame($email, $profile->email);

        $this->expectException(InvalidProfileData::class);
        app(UpdatePersonalData::class)->execute($id, '', 'Pérez', null);
    }

    public function test_pantalla_mi_perfil(): void
    {
        $this->actingAs($this->user($this->register('Lucía')))
            ->get('/mi-perfil')
            ->assertOk()
            ->assertSee('Lucía');
    }
}
