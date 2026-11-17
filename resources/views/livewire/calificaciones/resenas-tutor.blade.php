<div class="rt">
    <style>
        .rt { display: grid; gap: 16px; max-width: 1000px; }
        .rt .barra { display: flex; justify-content: flex-end; }
        .rt .barra select { padding: 9px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; background: white; min-width: 260px; max-width: 100%; }
        .rt .grid { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 16px; align-items: start; }
        .rt .panel { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .rt h2 { margin: 0 0 12px; font-size: 17px; color: #172b4d; }
        .rt .promedio { font-size: 44px; font-weight: 800; color: #172b4d; line-height: 1; }
        .rt .estrellas { color: #e0a100; letter-spacing: 1px; }
        .rt .sub { color: #65738a; font-size: 13.5px; }
        .rt .barra-dist { display: grid; grid-template-columns: 34px 1fr 30px; gap: 8px; align-items: center; font-size: 13px; color: #52657e; margin-top: 6px; }
        .rt .pista { height: 8px; background: #eef2f7; border-radius: 4px; overflow: hidden; }
        .rt .relleno { height: 100%; background: #e0a100; border-radius: 4px; }
        .rt .metrica { display: flex; justify-content: space-between; gap: 10px; padding: 9px 0; border-top: 1px solid #eef2f7; font-size: 14px; }
        .rt .metrica:first-of-type { border-top: 0; }
        .rt .metrica strong { color: #172b4d; white-space: nowrap; }
        .rt .resena { border-top: 1px solid #eef2f7; padding: 14px 0; }
        .rt .resena:first-of-type { border-top: 0; padding-top: 0; }
        .rt .resena p { margin: 6px 0 0; color: #3a4a60; line-height: 1.55; }
        .rt .chip { display: inline-block; background: #e7f2fb; color: #1d5a86; border-radius: 999px; padding: 2px 9px; font-size: 12px; margin-left: 6px; }
        .rt .sugerencias { margin: 0; padding-left: 18px; color: #3a4a60; font-size: 14px; line-height: 1.6; }
        .rt .vacio { color: #718096; text-align: center; padding: 26px; }
        @media (max-width: 820px) { .rt .grid { grid-template-columns: 1fr; } }
    </style>

    @php $max = max(1, max($datos['distribution'])); @endphp

    <div class="barra">
        <select wire:model.live="curso" aria-label="Filtrar por tutoría">
            <option value="">Todas mis tutorías</option>
            @foreach ($cursos as $c) <option value="{{ $c->id }}">{{ $c->title }}</option> @endforeach
        </select>
    </div>

    @if ($datos['count'] === 0)
        <div class="panel vacio">
            Todavía no tienes calificaciones{{ $curso !== '' ? ' en esta tutoría' : '' }}.
            Los estudiantes pueden calificarte cuando registras su asistencia y ellos confirman su participación.
        </div>
    @else
        <div class="grid">
            <div style="display: grid; gap: 16px;">
                <section class="panel">
                    <h2>Calificación promedio</h2>
                    <div class="promedio">{{ number_format($datos['average'], 1) }}</div>
                    <div class="estrellas" aria-hidden="true">{{ str_repeat('★', (int) round($datos['average'])) }}{{ str_repeat('☆', 5 - (int) round($datos['average'])) }}</div>
                    <div class="sub">{{ $datos['count'] }} calificación(es)</div>
                    <div style="margin-top: 12px;">
                        @foreach ($datos['distribution'] as $estrellas => $n)
                            <div class="barra-dist">
                                <span>{{ $estrellas }}★</span>
                                <div class="pista" role="img" aria-label="{{ $n }} calificaciones de {{ $estrellas }} estrellas"><div class="relleno" style="width: {{ round($n * 100 / $max) }}%"></div></div>
                                <span>{{ $n }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="panel">
                    <h2>Encuesta de satisfacción</h2>
                    @if ($datos['survey']['responses'] === 0)
                        <p class="sub" style="margin: 0;">Aún no hay encuestas respondidas.</p>
                    @else
                        <div class="sub" style="margin-bottom: 8px;">{{ $datos['survey']['responses'] }} respuesta(s)</div>
                        @foreach ($datos['survey']['scales'] as $s)
                            <div class="metrica"><span>{{ $s['text'] }}</span><strong>{{ $s['average'] !== null ? number_format($s['average'], 1).' / 5' : '—' }}</strong></div>
                        @endforeach
                        <div class="metrica"><span>Lo recomendarían</span><strong>{{ $datos['survey']['recommend'] }}%</strong></div>
                    @endif
                </section>
            </div>

            <div style="display: grid; gap: 16px;">
                <section class="panel">
                    <h2>Reseñas</h2>
                    @foreach ($datos['reviews'] as $r)
                        <div class="resena" wire:key="resena-{{ $r->id }}">
                            <span class="estrellas" aria-label="{{ $r->score }} de 5">{{ str_repeat('★', $r->score) }}{{ str_repeat('☆', 5 - $r->score) }}</span>
                            <span class="sub">· {{ $r->studentName }} · {{ $r->date }}</span>
                            @if ($curso === '') <span class="chip">{{ $r->courseTitle }}</span> @endif
                            <p>{{ $r->comment }}</p>
                        </div>
                    @endforeach
                </section>

                @if (count($datos['survey']['suggestions']) > 0)
                    <section class="panel">
                        <h2>Sugerencias de mejora</h2>
                        <ul class="sugerencias">
                            @foreach ($datos['survey']['suggestions'] as $s) <li>{{ $s }}</li> @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    @endif
</div>
