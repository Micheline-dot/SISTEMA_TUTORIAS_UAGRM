<?php

namespace App\DataAccess\PostgreSQL\Users\Security;

use App\Business\Usuarios\Contracts\SessionAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LaravelSessionAuthenticator implements SessionAuthenticator
{
    public function __construct(private readonly Request $request)
    {
    }

    public function attempt(string $email, string $plainTextPassword): ?string
    {
        $authenticated = Auth::attempt([
            'correo' => $email,
            'password' => $plainTextPassword,
            'activo' => true,
        ]);

        if (! $authenticated) {
            return null;
        }

        $this->request->session()->regenerate();

        return (string) Auth::id();
    }

    public function logout(): void
    {
        Auth::logout();
        $this->request->session()->invalidate();
        $this->request->session()->regenerateToken();
    }
}
