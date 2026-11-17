<div class="rp">
    @include('livewire.reportes._estilos')

    @php
        $t = $r['totales'];
        $estrellas = fn (float $p) => str_repeat('★', (int) round($p)).str_repeat('☆', 5 - (int) round($p));
        $categorias = array_slice(array_values(array_filter($r['categorias'], fn ($c) => $c['inscripciones'] > 0 || $c['tutorias'] > 0)), 0, 8);
        $maxCat = max(1, ...array_column($categorias ?: [['inscripciones' => 0]], 'inscripciones'));
        $maxFac = max(1, ...array_column($r['facultades'] ?: [['inscripciones' => 0]], 'inscripciones'));
    @endphp

    @include('livewire.reportes._periodo')
    <div class="periodo">Periodo: <strong>{{ $periodo }}</strong>. El periodo filtra inscripciones y calificaciones; las tutorías y usuarios muestran el estado actual.</div>

    <div class="kpis">
        <div class="kpi"><div class="valor">{{ $t['usuarios_activos'] }}</div><div class="nombre">Usuarios activos</div><div class="extra">{{ $t['estudiantes'] }} estudiantes · {{ $t['tutores'] }} tutores</div></div>
        <div class="kpi"><div class="valor">{{ $t['cursos']['PUBLICADO'] }}</div><div class="nombre">Tutorías publicadas</div><div class="extra">{{ $t['cursos']['BORRADOR'] }} en borrador</div></div>
        <div class="kpi"><div class="valor">{{ $t['cursos']['FINALIZADO'] }}</div><div class="nombre">Tutorías concluidas</div><div class="extra">{{ $t['cursos']['CANCELADO'] }} canceladas</div></div>
        <div class="kpi"><div class="valor">{{ $t['inscripciones_efectivas'] }}</div><div class="nombre">Inscripciones</div><div class="extra">{{ $t['cancelaciones'] }} canceladas · {{ $t['confirmadas'] }} confirmadas</div></div>
        <div class="kpi"><div class="valor">{{ $t['promedio_calificacion'] ?? '—' }}</div><div class="nombre">Calificación promedio</div><div class="extra">{{ $t['calificaciones'] }} calificaciones</div></div>
        <div class="kpi"><div class="valor">{{ $t['solicitudes_pendientes'] }}</div><div class="nombre">Solicitudes de tutor pendientes</div></div>
    </div>

    <div class="destacados">
        @if ($r['mejor_tutor'])
            <div class="destacado"><div class="eti">Mejor tutor</div><div class="tit">{{ $r['mejor_tutor']['nombre'] }}</div><div class="det">{{ $r['mejor_tutor']['promedio'] }} ★ · {{ $r['mejor_tutor']['calificaciones'] }} calificaciones</div></div>
        @else
            <div class="destacado vacio"><div class="eti">Mejor tutor</div><div class="tit">Sin calificaciones</div></div>
        @endif
        @if ($r['categoria_mas_solicitada'])
            <div class="destacado"><div class="eti">Categoría más solicitada</div><div class="tit">{{ $r['categoria_mas_solicitada']['nombre'] }}</div><div class="det">{{ $r['categoria_mas_solicitada']['inscripciones'] }} inscripciones · {{ $r['categoria_mas_solicitada']['tutorias'] }} tutorías</div></div>
        @else
            <div class="destacado vacio"><div class="eti">Categoría más solicitada</div><div class="tit">Sin inscripciones</div></div>
        @endif
        @if ($r['facultad_mas_solicitada'])
            <div class="destacado"><div class="eti">Facultad más solicitada</div><div class="tit">{{ $r['facultad_mas_solicitada']['nombre'] }}</div><div class="det">{{ $r['facultad_mas_solicitada']['inscripciones'] }} inscripciones · {{ $r['facultad_mas_solicitada']['estudiantes'] }} estudiantes</div></div>
        @else
            <div class="destacado vacio"><div class="eti">Facultad más solicitada</div><div class="tit">Sin inscripciones</div></div>
        @endif
    </div>

    <section class="panel">
        <h2>Mejores tutores</h2>
        @if ($r['mejores_tutores'] === [])
            <div class="vacio-txt">Todavía no hay tutores calificados en este periodo.</div>
        @else
            <div class="tabla">
                <table>
                    <thead><tr><th>#</th><th>Tutor</th><th>Calificación</th><th class="num">Calificaciones</th><th class="num">Tutorías</th><th class="num">Inscritos</th></tr></thead>
                    <tbody>
                        @foreach ($r['mejores_tutores'] as $n => $x)
                            <tr wire:key="tutor-{{ $x['id'] }}">
                                <td>{{ $n + 1 }}</td>
                                <td>{{ $x['nombre'] }}</td>
                                <td><span class="estrellas" aria-hidden="true">{{ $estrellas($x['promedio']) }}</span> {{ $x['promedio'] }}</td>
                                <td class="num">{{ $x['calificaciones'] }}</td>
                                <td class="num">{{ $x['tutorias'] }}</td>
                                <td class="num">{{ $x['inscritos'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="grid2">
        <section class="panel">
            <h2>Inscripciones por categoría</h2>
            @forelse ($categorias as $c)
                <div class="fila" title="{{ $c['tutorias'] }} tutorías">
                    <span class="n">{{ $c['nombre'] }}</span>
                    <span class="pista"><span class="relleno" style="display:block;width: {{ round($c['inscripciones'] * 100 / $maxCat) }}%"></span></span>
                    <span class="v">{{ $c['inscripciones'] }}</span>
                </div>
            @empty
                <div class="vacio-txt">No hay tutorías publicadas todavía.</div>
            @endforelse
        </section>

        <section class="panel">
            <h2>Inscripciones por facultad</h2>
            @forelse ($r['facultades'] as $f)
                <div class="fila" title="{{ $f['estudiantes'] }} estudiantes">
                    <span class="n">{{ $f['nombre'] }}</span>
                    <span class="pista"><span class="relleno" style="display:block;width: {{ round($f['inscripciones'] * 100 / $maxFac) }}%"></span></span>
                    <span class="v">{{ $f['inscripciones'] }}</span>
                </div>
            @empty
                <div class="vacio-txt">No hay inscripciones en este periodo.</div>
            @endforelse
        </section>
    </div>
</div>
