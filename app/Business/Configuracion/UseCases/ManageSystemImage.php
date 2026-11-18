<?php

namespace App\Business\Configuracion\UseCases;

use App\Business\Configuracion\Contracts\LogoStorage;
use App\Business\Configuracion\Contracts\SystemSettingsRepository;
use App\Business\Configuracion\Exceptions\InvalidLogo;
use App\Business\Usuarios\Contracts\RoleAssignmentRepository;

/**
 * (+) Imagen institucional configurable por un administrador (logo, fondo del login…).
 * Cada subclase define su clave de configuración y su nombre. No se aceptan SVG
 * (podrían contener scripts).
 */
abstract class ManageSystemImage
{
    public const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];

    public function __construct(
        private readonly SystemSettingsRepository $settings,
        private readonly LogoStorage $storage,
        private readonly RoleAssignmentRepository $roles,
    ) {
    }

    /** Clave en la tabla configuracion. */
    abstract protected function key(): string;

    /** Nombre para los mensajes ("El logo…", "El fondo…"). */
    abstract protected function label(): string;

    public function update(string $adminId, string $temporaryPath, string $extension): void
    {
        $this->assertAdmin($adminId);

        $extension = strtolower($extension);
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidLogo(ucfirst($this->label()).' debe ser una imagen PNG, JPG o WEBP.');
        }

        $previous = $this->settings->get($this->key());
        $newPath = $this->storage->store($temporaryPath, $extension, $this->key());

        try {
            $this->settings->set($this->key(), $newPath);
        } catch (\Throwable $e) {
            $this->storage->delete($newPath);
            throw $e;
        }

        if ($previous !== null) {
            $this->storage->delete($previous);
        }
    }

    public function remove(string $adminId): void
    {
        $this->assertAdmin($adminId);

        $previous = $this->settings->get($this->key());
        if ($previous === null) {
            return;
        }

        $this->settings->set($this->key(), null);
        $this->storage->delete($previous);
    }

    /** Ruta vigente, o null si no hay (o el archivo ya no existe). */
    public function currentPath(): ?string
    {
        $path = $this->settings->get($this->key());

        return $path !== null && $this->storage->exists($path) ? $path : null;
    }

    public function exists(): bool
    {
        return $this->currentPath() !== null;
    }

    private function assertAdmin(string $adminId): void
    {
        if (! $this->roles->hasRole($adminId, 'ADMINISTRADOR')) {
            throw new InvalidLogo('Solo un administrador puede cambiar '.$this->label().'.');
        }
    }
}
