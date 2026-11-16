<?php

namespace App\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Control de acceso por rol a nivel de ruta.
 * Uso: ->middleware('rol:ADMINISTRADOR') o 'rol:ESTUDIANTE,TUTOR' (cualquiera de ellos).
 * Las reglas de negocio vuelven a verificar permisos en cada caso de uso.
 */
final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        // Una cuenta desactivada pierde el acceso aunque tenga sesión abierta.
        if (! $user->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        foreach ($roles as $rol) {
            if ($user->tieneRol(strtoupper(trim($rol)))) {
                return $next($request);
            }
        }

        abort(403, 'No tienes autorización para acceder a esta sección.');
    }
}
