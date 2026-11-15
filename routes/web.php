
<?php

use Illuminate\Support\Facades\Route;

use App\Presentation\Http\Controllers\LogoutController;
use App\Presentation\Http\Controllers\DocumentoSolicitudHabilitacionController;

use App\Presentation\Livewire\Auth\Login;
use App\Presentation\Livewire\Auth\Register;

use App\Presentation\Livewire\Habilitaciones\SolicitarHabilitacion;
use App\Presentation\Livewire\Habilitaciones\GestionarSolicitudesHabilitacion;

// Página principal
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// Rutas para usuarios que NO iniciaron sesión
Route::middleware('guest')->group(function () {

    Route::get('/registro', Register::class)
        ->name('register');

    Route::get('/login', Login::class)
        ->name('login');

});

// Rutas para usuarios que SÍ iniciaron sesión
Route::middleware('auth')->group(function () {

    // Panel principal
    Route::get('/inicio', function () {
        return view('auth.home');
    })->name('home');

    // Solicitar habilitación como tutor
    Route::get(
        '/solicitar-habilitacion',
        SolicitarHabilitacion::class
    )->name('habilitacion.solicitar');

    // Gestionar solicitudes de tutor
    Route::get(
        '/administracion/solicitudes-tutor',
        GestionarSolicitudesHabilitacion::class
    )->name('admin.solicitudes-tutor');

    // Visualizar documentos PDF de las solicitudes
    Route::get(
        '/administracion/solicitudes-tutor/{idSolicitud}/documentos/{tipoDocumento}',
        DocumentoSolicitudHabilitacionController::class
    )->whereNumber('idSolicitud')
     ->name('admin.solicitudes-tutor.documento');

    // Cerrar sesión
    Route::post('/logout', LogoutController::class)
        ->name('logout');

});
