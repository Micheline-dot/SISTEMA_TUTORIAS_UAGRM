{{--
    Menú lateral por rol con secciones desplegables (<details>).
    La sección que contiene la pantalla actual se abre sola.
    Los props itemClass/titleClass se conservan por compatibilidad con los layouts.
--}}
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

    $noLeidas = $usuario
        ? app(\App\Business\Notificaciones\UseCases\GestionarMisNotificaciones::class)->contarNoLeidas((string) $usuario->getKey())
        : 0;

    // Íconos (trazos SVG de 24x24).
    $iconos = [
        'inicio' => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>',
        'perfil' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'campana' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
        'buscar' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'lista' => '<path d="M9 6h11M9 12h11M9 18h11"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
        'mas' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'libro' => '<path d="M4 19V5a2 2 0 0 1 2-2h14v16H6a2 2 0 0 0-2 2z"/><path d="M8 7h8"/>',
        'birrete' => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5"/>',
        'documento' => '<path d="M14 3H6v18h12V7z"/><path d="M14 3v4h4M9 13h6M9 17h6"/>',
        'usuarios' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5.5 7-5.5s7 2 7 5.5"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5c2.5.6 4 2.5 4 5.5"/>',
        'etiqueta' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
        'calendario' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'estudiante' => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M22 9v6"/>',
        'tutor' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
        'admin' => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/>',
    ];

    // [título, ícono, [[ruta, etiqueta, patrones activos, ícono, insignia]]]
    $secciones = [[
        'Menú principal', 'menu', [
            ['home', 'Inicio', 'home', 'inicio', null],
            ['perfil', 'Mi perfil', 'perfil', 'perfil', null],
            ['notificaciones', 'Notificaciones', 'notificaciones', 'campana', $noLeidas],
        ],
    ]];

    if ($esEstudiante) {
        $items = [
            ['estudiante.catalogo', 'Buscar tutorías', ['estudiante.catalogo', 'estudiante.tutoria'], 'buscar', null],
            ['estudiante.inscripciones', 'Mis inscripciones', 'estudiante.inscripciones', 'lista', null],
        ];

        if (! $esTutor) {
            $items[] = ['habilitacion.solicitar', 'Quiero ser tutor', 'habilitacion.*', 'mas', null];
        }

        $secciones[] = ['Área de estudiante', 'estudiante', $items];
    }

    if ($esTutor) {
        $secciones[] = ['Área de tutor', 'tutor', [
            ['tutor.tutorias', 'Mis tutorías', ['tutor.tutorias', 'tutor.tutoria', 'tutor.asistencia'], 'calendario', null],
            ['tutor.perfil-academico', 'Perfil académico', 'tutor.perfil-academico', 'birrete', null],
        ]];
    }

    if ($esAdministrador) {
        $secciones[] = ['Administración', 'admin', [
            ['admin.solicitudes-tutor', 'Solicitudes de tutor', 'admin.solicitudes-tutor*', 'documento', null],
            ['admin.tutorias', 'Gestionar tutorías', 'admin.tutorias', 'libro', null],
            ['admin.usuarios', 'Gestionar usuarios', 'admin.usuarios', 'usuarios', null],
            ['admin.categorias', 'Categorías', 'admin.categorias', 'etiqueta', null],
        ]];
    }

    // ¿Qué sección contiene la pantalla actual?
    $activa = null;
    foreach ($secciones as $i => [, , $items]) {
        foreach ($items as [, , $patron]) {
            if (request()->routeIs(...(array) $patron)) {
                $activa = $i;
            }
        }
    }
    $activa ??= 0;

    $svg = fn (string $nombre, int $tam = 18) => '<svg width="'.$tam.'" height="'.$tam.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$iconos[$nombre].'</svg>';
@endphp

<style>
    .menu-rol { display: grid; gap: 6px; max-height: calc(100vh - 175px); overflow-y: auto; margin-right: -6px; padding-right: 6px; scrollbar-width: thin; scrollbar-color: #2e5582 transparent; }
    .menu-rol details { border-radius: 10px; }
    .menu-rol details[open] { background: #ffffff0a; }
    .menu-rol summary { list-style: none; display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 10px; cursor: pointer; color: #b9c8db; font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; user-select: none; }
    .menu-rol summary::-webkit-details-marker { display: none; }
    .menu-rol summary:hover { background: #ffffff12; color: #fff; }
    .menu-rol summary:focus-visible, .menu-rol a:focus-visible { outline: 2px solid #65c3ed; outline-offset: 2px; }
    .menu-rol summary .flecha { margin-left: auto; transition: transform .2s; opacity: .8; }
    .menu-rol details[open] summary .flecha { transform: rotate(90deg); }
    .menu-rol details[open] summary { color: #fff; }
    .menu-rol .opciones { display: grid; gap: 2px; padding: 2px 6px 8px 14px; }
    .menu-rol .opciones a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: #dbe5f1; text-decoration: none; font-size: 14.5px; border-left: 3px solid transparent; }
    .menu-rol .opciones a:hover { background: #24466f; color: #fff; }
    .menu-rol .opciones a.active { background: #24466f; color: #fff; border-left-color: #65c3ed; font-weight: 600; }
    .menu-rol .opciones a svg { flex: none; opacity: .9; }
    .menu-rol .insignia { margin-left: auto; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px; background: #d92d20; color: #fff; font-size: 11px; font-weight: 700; display: grid; place-items: center; }
    @media (max-width: 600px) { .menu-rol { max-height: none; overflow: visible; } }
</style>

<nav class="menu-rol" aria-label="Menú principal">
    @foreach ($secciones as $i => [$titulo, $icono, $items])
        <details @if ($i === $activa) open @endif>
            <summary>
                {!! $svg($icono, 17) !!}
                <span>{{ $titulo }}</span>
                <svg class="flecha" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
            </summary>
            <div class="opciones">
                @foreach ($items as [$ruta, $etiqueta, $patron, $iconoItem, $insignia])
                    @php $esActual = request()->routeIs(...(array) $patron); @endphp
                    <a href="{{ route($ruta) }}" class="{{ $esActual ? 'active' : '' }}" @if ($esActual) aria-current="page" @endif>
                        {!! $svg($iconoItem) !!}
                        <span>{{ $etiqueta }}</span>
                        @if ($insignia)
                            <span class="insignia" aria-label="{{ $insignia }} sin leer">{{ $insignia > 99 ? '99+' : $insignia }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </details>
    @endforeach
</nav>
