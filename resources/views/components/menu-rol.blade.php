@props([
    'itemClass' => 'nav-item',
    'titleClass' => 'nav-title',
])

@php
    $usuario = auth()->user();
    $roles = $usuario?->nombresRoles() ?? [];

    $esEstudiante = in_array('ESTUDIANTE', $roles, true);
    $esTutor = in_array('TUTOR', $roles, true);
    $esAdministrador = in_array('ADMINISTRADOR', $roles, true);

    // [rol requerido, título de sección, [ruta, etiqueta, patrón de ruta activa]]
    $secciones = [];

    if ($esEstudiante) {
        $items = [
            ['estudiante.catalogo', 'Buscar tutorías', 'estudiante.catalogo'],
            ['estudiante.inscripciones', 'Mis inscripciones', 'estudiante.inscripciones'],
        ];

        if (! $esTutor) {
            $items[] = ['habilitacion.solicitar', 'Quiero ser tutor', 'habilitacion.*'];
        }

        $secciones[] = ['Área de estudiante', $items];
    }

    if ($esTutor) {
        $secciones[] = ['Área de tutor', [
            ['tutor.tutorias', 'Mis tutorías', ['tutor.tutorias', 'tutor.tutoria', 'tutor.asistencia']],
            ['tutor.perfil-academico', 'Perfil académico', 'tutor.perfil-academico'],
        ]];
    }

    if ($esAdministrador) {
        $secciones[] = ['Administración', [
            ['admin.solicitudes-tutor', 'Solicitudes de tutor', 'admin.solicitudes-tutor*'],
            ['admin.tutorias', 'Gestionar tutorías', 'admin.tutorias'],
            ['admin.usuarios', 'Gestionar usuarios', 'admin.usuarios'],
            ['admin.categorias', 'Categorías', 'admin.categorias'],
        ]];
    }
@endphp

<div class="{{ $titleClass }}">Menú principal</div>
<a class="{{ $itemClass }} {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
    Inicio
</a>
<a class="{{ $itemClass }} {{ request()->routeIs('perfil') ? 'active' : '' }}" href="{{ route('perfil') }}">
    Mi perfil
</a>
<a class="{{ $itemClass }} {{ request()->routeIs('notificaciones') ? 'active' : '' }}" href="{{ route('notificaciones') }}">
    Notificaciones
</a>

@foreach ($secciones as [$titulo, $items])
    <div class="{{ $titleClass }}">{{ $titulo }}</div>

    @foreach ($items as [$ruta, $etiqueta, $patron])
        <a class="{{ $itemClass }} {{ request()->routeIs(...(array) $patron) ? 'active' : '' }}" href="{{ route($ruta) }}">
            {{ $etiqueta }}
        </a>
    @endforeach
@endforeach
