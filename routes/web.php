<?php

use App\Presentation\Http\Controllers\DocumentoSolicitudHabilitacionController;
use App\Presentation\Http\Controllers\FotoPerfilController;
use App\Presentation\Http\Controllers\LogoutController;
use App\Presentation\Livewire\Notificaciones\MisNotificaciones;
use App\Presentation\Livewire\Tutor\PerfilAcademico;
use App\Presentation\Livewire\Usuarios\MiPerfil;
use App\Presentation\Livewire\Administracion\GestionarTutorias;
use App\Presentation\Livewire\Administracion\GestionarUsuarios;
use App\Presentation\Livewire\Administracion\GestionarCategorias;
use App\Presentation\Livewire\Cursos\GestionarTutoria;
use App\Presentation\Livewire\Auth\Login;
use App\Presentation\Livewire\Auth\Register;
use App\Presentation\Livewire\Cursos\CatalogoTutorias;
use App\Presentation\Livewire\Cursos\MisTutorias;
use App\Presentation\Livewire\Habilitaciones\GestionarSolicitudesHabilitacion;
use App\Presentation\Livewire\Habilitaciones\SolicitarHabilitacion;
use App\Presentation\Livewire\Inscripciones\GestionarAsistencia;
use App\Presentation\Livewire\Inscripciones\MisInscripciones;
use Illuminate\Support\Facades\Route;

// Página principal
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// Invitados (sin sesión) — CU01
Route::middleware('guest')->group(function () {
    Route::get('/registro', Register::class)->name('register');
    Route::get('/login', Login::class)->name('login');
});

// Usuarios autenticados
Route::middleware('auth')->group(function () {

    Route::get('/inicio', fn () => view('auth.home'))->name('home');
    Route::post('/logout', LogoutController::class)->name('logout');

    // Notificaciones — CU19 (todos los roles)
    Route::get('/notificaciones', MisNotificaciones::class)->name('notificaciones');

    // Mi perfil — CU02 (todos los roles)
    Route::get('/mi-perfil', MiPerfil::class)->name('perfil');
    Route::get('/usuarios/{userId}/foto', FotoPerfilController::class)
        ->whereNumber('userId')
        ->name('perfil.foto');

    // Área de estudiante — CU03, CU11–CU14
    Route::middleware('rol:ESTUDIANTE')->group(function () {
        Route::get('/solicitar-habilitacion', SolicitarHabilitacion::class)
            ->name('habilitacion.solicitar');
        Route::get('/estudiante/buscar-tutorias', CatalogoTutorias::class)
            ->name('estudiante.catalogo');
        Route::get('/estudiante/mis-inscripciones', MisInscripciones::class)
            ->name('estudiante.inscripciones');
    });

    // Área de tutor — CU07–CU10, CU15
    Route::middleware('rol:TUTOR')->group(function () {
        Route::get('/tutor/mis-tutorias', MisTutorias::class)
            ->name('tutor.tutorias');
        Route::get('/tutor/perfil-academico', PerfilAcademico::class)
            ->name('tutor.perfil-academico');
        Route::get('/tutor/tutorias/{courseId}', GestionarTutoria::class)
            ->whereNumber('courseId')
            ->name('tutor.tutoria');
        Route::get('/tutor/tutorias/{courseId}/asistencia', GestionarAsistencia::class)
            ->whereNumber('courseId')
            ->name('tutor.asistencia');
    });

    // Administración — CU04, CU05, CU10 (cancelación)
    Route::middleware('rol:ADMINISTRADOR')->prefix('administracion')->group(function () {
        Route::get('/solicitudes-tutor', GestionarSolicitudesHabilitacion::class)
            ->name('admin.solicitudes-tutor');
        Route::get('/solicitudes-tutor/{idSolicitud}/documentos/{tipoDocumento}', DocumentoSolicitudHabilitacionController::class)
            ->whereNumber('idSolicitud')
            ->name('admin.solicitudes-tutor.documento');
        Route::get('/tutorias', GestionarTutorias::class)
            ->name('admin.tutorias');
        Route::get('/usuarios', GestionarUsuarios::class)
            ->name('admin.usuarios');
        Route::get('/categorias', GestionarCategorias::class)
            ->name('admin.categorias');
    });
});
