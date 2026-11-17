<?php

namespace App\Presentation\Livewire\Usuarios;

use App\Business\Usuarios\DTO\CompleteAcademicDataInput;
use App\Business\Usuarios\DTO\UserProfile;
use App\Business\Usuarios\Exceptions\AcademicDataAlreadyCompleted;
use App\Business\Usuarios\Exceptions\DuplicateAcademicCode;
use App\Business\Usuarios\Exceptions\InvalidProfileData;
use App\Business\Usuarios\UseCases\CompleteAcademicData;
use App\Business\Usuarios\UseCases\FacultyNotFound;
use App\Business\Usuarios\UseCases\GetUserProfile;
use App\Business\Usuarios\UseCases\ListFaculties;
use App\Business\Usuarios\UseCases\UpdatePersonalData;
use App\Business\Usuarios\UseCases\UpdateProfilePhoto;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * CU02 Gestionar perfil de usuario.
 *  - Editables siempre: foto, nombres, apellidos, teléfono.
 *  - Fijo: correo.
 *  - Una sola vez: facultad + tipo de registro con su registro/código.
 */
final class MiPerfil extends Component
{
    use WithFileUploads;

    public string $firstNames = '';

    public string $lastNames = '';

    public ?string $phone = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $photo = null;

    public string $faculty = '';

    /** Estudiante por defecto: así el campo de registro universitario se ve desde el inicio. */
    public string $registrationType = 'ESTUDIANTE';

    public string $studentRegistry = '';

    public string $assistantRegistry = '';

    public string $teacherCode = '';

    protected GetUserProfile $getProfile;

    protected ListFaculties $listFaculties;

    public function boot(GetUserProfile $getProfile, ListFaculties $listFaculties): void
    {
        $this->getProfile = $getProfile;
        $this->listFaculties = $listFaculties;
    }

    public function mount(): void
    {
        $profile = $this->getProfile->execute($this->userId());

        $this->firstNames = $profile->firstNames;
        $this->lastNames = $profile->lastNames;
        $this->phone = $profile->phone ?? '';
    }

    public function guardarDatosPersonales(UpdatePersonalData $useCase): void
    {
        $this->validate([
            'firstNames' => ['required', 'string', 'max:100'],
            'lastNames' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9 \-]{7,20}$/'],
        ], [
            'phone.regex' => 'Ingresa un teléfono válido (solo números, de 7 a 15 dígitos).',
        ], [
            'firstNames' => 'nombres',
            'lastNames' => 'apellidos',
            'phone' => 'teléfono',
        ]);

        try {
            $useCase->execute($this->userId(), $this->firstNames, $this->lastNames, $this->phone);
            session()->flash('perfil_ok', 'Tus datos personales se actualizaron.');
        } catch (InvalidProfileData) {
            $this->addError('phone', 'Revisa los datos ingresados.');
        }
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo', [
            'photo' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.UpdateProfilePhoto::MAX_KILOBYTES],
        ], [
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La foto debe ser JPG, PNG o WEBP.',
            'photo.max' => 'La foto no puede superar los 2 MB.',
        ]);
    }

    public function guardarFoto(UpdateProfilePhoto $useCase): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.UpdateProfilePhoto::MAX_KILOBYTES],
        ], [
            'photo.required' => 'Selecciona una imagen.',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La foto debe ser JPG, PNG o WEBP.',
            'photo.max' => 'La foto no puede superar los 2 MB.',
        ]);

        try {
            $useCase->execute($this->userId(), $this->photo->getRealPath(), $this->photo->guessExtension() ?? '');
            $this->reset('photo');
            session()->flash('perfil_ok', 'Tu foto de perfil se actualizó.');
        } catch (InvalidProfileData) {
            $this->addError('photo', 'La foto debe ser JPG, PNG o WEBP.');
        }
    }

    public function quitarFoto(UpdateProfilePhoto $useCase): void
    {
        $useCase->remove($this->userId());
        session()->flash('perfil_ok', 'Se quitó tu foto de perfil.');
    }

    public function guardarDatosAcademicos(CompleteAcademicData $useCase): void
    {
        $this->validate($this->academicRules(), [
            'studentRegistry.regex' => 'El registro universitario debe tener solo números (6 a 15 dígitos).',
            'assistantRegistry.regex' => 'Usa solo letras, números o guiones (3 a 30 caracteres).',
            'teacherCode.regex' => 'Usa solo letras, números o guiones (3 a 30 caracteres).',
        ], [
            'faculty' => 'facultad',
            'registrationType' => 'tipo de registro',
            'studentRegistry' => 'registro universitario',
            'assistantRegistry' => 'código de auxiliar',
            'teacherCode' => 'código de docente',
        ]);

        try {
            $useCase->execute(new CompleteAcademicDataInput(
                $this->userId(),
                $this->faculty,
                $this->registrationType,
                $this->studentRegistry,
                $this->assistantRegistry,
                $this->teacherCode,
            ));

            $this->reset('faculty', 'registrationType', 'studentRegistry', 'assistantRegistry', 'teacherCode');
            session()->flash('perfil_ok', 'Tus datos académicos se registraron.');
        } catch (DuplicateAcademicCode $e) {
            $this->addError($e->field, match ($e->field) {
                DuplicateAcademicCode::STUDENT_REGISTRY => 'Este registro universitario ya pertenece a otra cuenta.',
                DuplicateAcademicCode::ASSISTANT_REGISTRY => 'Este código de auxiliar ya pertenece a otra cuenta.',
                default => 'Este código de docente ya pertenece a otra cuenta.',
            });
        } catch (FacultyNotFound) {
            $this->addError('faculty', 'La facultad seleccionada no pertenece al catálogo.');
        } catch (AcademicDataAlreadyCompleted) {
            session()->flash('perfil_error', 'Tus datos académicos ya fueron registrados y no pueden modificarse.');
        } catch (InvalidProfileData) {
            $this->addError('registrationType', 'Revisa los datos académicos ingresados.');
        }
    }

    /** @return array<string, list<string>> */
    private function academicRules(): array
    {
        $type = $this->registrationType;

        return [
            'faculty' => ['required', 'string'],
            'registrationType' => ['required', 'in:'.implode(',', CompleteAcademicData::TYPES)],
            'studentRegistry' => in_array($type, ['ESTUDIANTE', 'AUXILIAR'], true)
                ? ['required', 'regex:/^[0-9]{6,15}$/'] : ['nullable'],
            'assistantRegistry' => $type === 'AUXILIAR'
                ? ['required', 'regex:/^[A-Za-z0-9-]{3,30}$/'] : ['nullable'],
            'teacherCode' => $type === 'DOCENTE'
                ? ['required', 'regex:/^[A-Za-z0-9-]{3,30}$/'] : ['nullable'],
        ];
    }

    private function userId(): string
    {
        return (string) auth()->id();
    }

    public function render(): View
    {
        /** @var UserProfile $profile */
        $profile = $this->getProfile->execute($this->userId());

        return view('livewire.usuarios.mi-perfil', [
            'profile' => $profile,
            'faculties' => $profile->academicDataCompleted() ? [] : $this->listFaculties->execute(),
        ])->layout($profile->isAdministrator() ? 'layouts.admin' : 'layouts.student')
            ->title('Mi perfil');
    }
}
