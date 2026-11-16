
<?php

use Illuminate\Support\Facades\Route;

use App\Presentation\Http\Controllers\LogoutController;
use App\Presentation\Http\Controllers\DocumentoSolicitudHabilitacionController;

use App\Presentation\Livewire\Auth\Login;
use App\Presentation\Livewire\Auth\Register;

use App\Presentation\Livewire\Habilitaciones\SolicitarHabilitacion;
use App\Presentation\Livewire\Habilitaciones\GestionarSolicitudesHabilitacion;
use App\Presentation\Livewire\Cursos\MisTutorias;
use App\Presentation\Livewire\Cursos\CatalogoTutorias;
use App\Presentation\Livewire\Inscripciones\MisInscripciones;
use App\Presentation\Livewire\Inscripciones\GestionarAsistencia;
use App\Presentation\Livewire\Administracion\GestionarTutorias;

// PÃ¡gina principal
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// Rutas para usuarios que NO iniciaron sesiÃ³n
Route::middleware('guest')->group(function () {

    Route::get('/registro', Register::class)
        ->name('register');

    Route::get('/login', Login::class)
        ->name('login');

});

// Rutas para usuarios que SÃ iniciaron sesiÃ³n
Route::middleware('auth')->group(function () {

    // Panel principal
    Route::get('/inicio', function () {
        return view('auth.home');
    })->name('home');

    // Solicitar habilitaciÃ³n como tutor
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

    // Gestionar tutorÃ­as propias
    Route::get('/tutor/mis-tutorias', MisTutorias::class)
        ->name('tutor.tutorias');
    // Cerrar sesiÃ³n
    Route::get('/estudiante/buscar-tutorias', CatalogoTutorias::class)
        ->name('estudiante.catalogo');
    Route::get('/estudiante/mis-inscripciones', MisInscripciones::class)
        ->name('estudiante.inscripciones');


    // Gestionar asistencia de estudiantes inscritos
    Route::get(
        '/tutor/tutorias/{courseId}/asistencia',
        GestionarAsistencia::class
    )->whereNumber('courseId')
     ->name('tutor.asistencia');
    // Administración general de tutorías
    Route::get(
        '/administracion/tutorias',
        GestionarTutorias::class
    )->name('admin.tutorias');
    Route::post('/logout', LogoutController::class)
        ->name('logout');

});
