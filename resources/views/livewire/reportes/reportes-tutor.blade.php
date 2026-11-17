<div class="rp">
    @include('livewire.reportes._estilos')

    @php
        $s = $r['resumen'];
        $mejor = $r['mejor_tutoria'];
        $fmt = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('d/m/Y') : '—';
        $maxIns = max(1, ...array_column($r['tutorias'] ?: [['inscritos' => 0]], 'inscritos'));
    @endphp

    @include('livewire.reportes._periodo')
    <div class="periodo">
        Periodo:
        <strong>{{ $r['periodo']->isAll() ? 'Todo el historial' : ($r['periodo']->from ?? 'inicio').' a '.($r['periodo']->to ?? 'hoy') }}</strong>.
        El periodo filtra inscripciones y calificaciones.
    </div>

    <div class="kpis">
        <div class="kpi"><div class="valor">{{ $s['publicadas'] }}</div><div class="nombre">Tutorías publicadas</div><div class="extra">{{ $s['borradores'] }} en borrador</div></div>
        <div class="kpi"><div class="valor">{{ $s['concluidas'] }}</div><div class="nombre">Tutorías concluidas</div><div class="extra">{{ $s['canceladas'] }} canceladas</div></div>
        <div class="kpi"><div class="valor">{{ $s['promedio_inscritos'] ?? '—' }}</div><div class="nombre">Promedio de inscritos</div><div class="extra">por tutoría publicada o concluida</div></div>
        <div class="kpi"><div class="valor">{{ $s['total_inscritos'] }}</div><div class="nombre">Total de inscritos</div><div class="extra">{{ $s['ocupacion_promedio'] !== null ? 'Ocupación media '.$s['ocupacion_promedio'].'%' : 'Sin cupos definidos' }}</div></div>
        <div class="kpi"><div class="valor">{{ $s['promedio_calificacion'] ?? '—' }}</div><div class="nombre">Calificación promedio</div><div class="extra">{{ $s['calificaciones'] }} calificaciones</div></div>
        <div class="kpi"><div class="valor">{{ $s['confirmados'] }}</div><div class="nombre">Participaciones confirmadas</div><div class="extra">{{ $s['cancelaciones'] }} cancelaciones</div></div>
    </div>

    <div class="destacados">
        @if ($mejor)
            <div class="destacado">
                <div class="eti">Mejor tutoría</div>
                <div class="tit">{{ $mejor['tutoria']['titulo'] }}</div>
                <div class="det">
                    @if ($mejor['criterio'] === 'CALIFICACION')
                        {{ $mejor['tutoria']['promedio'] }} ★ con {{ $mejor['tutoria']['calificaciones'] }} calificaciones · {{ $mejor['tutoria']['inscritos'] }} inscritos
                    @else
                        La de más inscritos ({{ $mejor['tutoria']['inscritos'] }}); aún no tiene calificaciones
                    @endif
                </div>
            </div>
        @else
            <div class="destacado vacio"><div class="eti">Mejor tutoría</div><div class="tit">Aún sin inscritos ni calificaciones</div></div>
        @endif
    </div>

    <section class="panel">
        <h2>Detalle por tutoría</h2>
        @if ($r['tutorias'] === [])
            <div class="vacio-txt">Todavía no publicaste tutorías. <a href="{{ route('tutor.tutorias') }}" wire:navigate>Ir a Mis tutorías</a></div>
        @else
            <div class="tabla">
                <table>
                    <thead>
                        <tr><th>Tutoría</th><th>Estado</th><th>Fechas</th><th class="num">Inscritos</th><th class="num">Ocupación</th><th class="num">Confirmados</th><th class="num">Cancel.</th><th class="num">Retirados</th><th class="num">Calificación</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($r['tutorias'] as $c)
                            <tr wire:key="curso-{{ $c['id'] }}">
                                <td><a href="{{ route('tutor.tutoria', $c['id']) }}" wire:navigate style="color:#174ea6;text-decoration:none;font-weight:600;">{{ $c['titulo'] }}</a>@if ($c['categoria'])<br><span style="color:#8a97ab;font-size:12px;">{{ $c['categoria'] }}</span>@endif</td>
                                <td><span class="chip {{ $c['estado'] }}">{{ $estados[$c['estado']] ?? $c['estado'] }}</span></td>
                                <td style="white-space:nowrap;">{{ $fmt($c['fecha_inicio']) }} – {{ $fmt($c['fecha_finalizacion']) }}</td>
                                <td class="num">{{ $c['inscritos'] }}{{ $c['cupos'] ? ' / '.$c['cupos'] : '' }}</td>
                                <td class="num">{{ $c['ocupacion'] !== null ? $c['ocupacion'].'%' : '—' }}</td>
                                <td class="num">{{ $c['confirmados'] }}</td>
                                <td class="num">{{ $c['cancelaciones'] }}</td>
                                <td class="num">{{ $c['retirados'] }}</td>
                                <td class="num">@if ($c['promedio'] !== null)<span class="estrellas">★</span> {{ $c['promedio'] }} ({{ $c['calificaciones'] }})@else — @endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if ($r['tutorias'] !== [])
        <section class="panel">
            <h2>Inscritos por tutoría</h2>
            @foreach (array_slice($r['tutorias'], 0, 10) as $c)
                <div class="fila">
                    <span class="n" title="{{ $c['titulo'] }}">{{ $c['titulo'] }}</span>
                    <span class="pista"><span class="relleno" style="display:block;width: {{ round($c['inscritos'] * 100 / $maxIns) }}%"></span></span>
                    <span class="v">{{ $c['inscritos'] }}</span>
                </div>
            @endforeach
        </section>
    @endif
</div>
