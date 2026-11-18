{{--
    Logo institucional configurable por el administrador (Administración → Logo del sistema).
    Sin logo subido se muestra el nombre del sistema en texto.
    tema:   oscuro (menú azul) | claro (login/registro)
    tamano: sm (menú) | lg (login/registro)
--}}
@props([
    'tema' => 'oscuro',
    'tamano' => 'sm',
])

@php
    $logos = app(\App\Business\Configuracion\UseCases\ManageSystemLogo::class);
    $ruta = $logos->currentPath();
@endphp

@once
<style>
    .logo-sis { display: flex; justify-content: center; align-items: center; width: 100%; }
    .logo-sis img { display: block; max-width: 100%; height: auto; object-fit: contain; }
    .logo-sis.sm img { max-height: 64px; }
    .logo-sis.lg img { max-height: 110px; }
    .logo-sis .texto { font-weight: 800; letter-spacing: .02em; line-height: 1.1; text-align: center; }
    .logo-sis.sm .texto { font-size: 21px; }
    .logo-sis.lg .texto { font-size: 26px; }
    .logo-sis.oscuro .texto { color: #fff; }
    .logo-sis.claro .texto { color: #172b4d; }
    .logo-sis .texto span { color: #65c3ed; }
    .logo-sis.claro .texto span { color: #2f86d6; }
</style>
@endonce

<span {{ $attributes->class(['logo-sis', $tema, $tamano]) }}>
    @if ($ruta)
        <img src="{{ route('sistema.logo', ['v' => substr(md5($ruta), 0, 8)]) }}" alt="Tutorías UAGRM">
    @else
        <span class="texto">TUTORÍAS <span>UAGRM</span></span>
    @endif
</span>
