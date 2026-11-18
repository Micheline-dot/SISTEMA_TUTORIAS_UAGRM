<div class="pt">
    <style>
        .pt { display: grid; gap: 18px; max-width: 1000px; }
        .pt .volver { color: #174ea6; text-decoration: none; font-weight: 600; font-size: 14px; background: none; border: 0; padding: 0; cursor: pointer; font: inherit; justify-self: start; }
        .pt .cab { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .pt .banda { height: 90px; background: linear-gradient(135deg, #174ea6, #3aa0d8); }
        .pt .cab-cuerpo { display: flex; gap: 18px; align-items: flex-end; padding: 0 24px 20px; margin-top: -46px; flex-wrap: wrap; }
        .pt .avatar { width: 104px; height: 104px; border-radius: 50%; border: 4px solid white; object-fit: cover; background: #e7f2fb; color: #21618e; display: grid; place-items: center; font-weight: 800; font-size: 40px; flex: none; }
        .pt h2 { margin: 0; font-size: 1.45rem; color: #172b4d; }
        .pt .sub { color: #65738a; font-size: 14px; }
        .pt .estrellas { color: #e0a100; letter-spacing: 1px; }
        .pt .layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 18px; align-items: start; }
        .pt .panel { background: white; border-radius: 12px; padding: 20px 22px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .pt h3 { margin: 0 0 10px; font-size: 1.05rem; color: #172b4d; }
        .pt .bio { color: #3a4a60; line-height: 1.65; white-space: pre-line; margin: 0; }
        .pt .chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .pt .chip { background: #e7f2fb; color: #1d5a86; border-radius: 999px; padding: 4px 10px; font-size: 13px; }
        .pt .enlaces { margin: 10px 0 0; padding-left: 18px; font-size: 14px; }
        .pt .enlaces a { color: #174ea6; overflow-wrap: anywhere; }
        .pt .resena { border-top: 1px solid #eef2f7; padding: 10px 0; }
        .pt .resena:first-of-type { border-top: 0; padding-top: 0; }
        .pt .resena p { margin: 4px 0 0; color: #3a4a60; font-size: 14px; line-height: 1.55; }
        .pt .aviso { background: #eef4ff; color: #1f4b8f; border-radius: 8px; padding: 10px 12px; font-size: 13.5px; }
        @media (max-width: 860px) { .pt .layout { grid-template-columns: 1fr; } }
    </style>

    @php
        $tipos = ['DOCENTE' => 'Docente', 'AUXILIAR' => 'Auxiliar de cátedra', 'ESTUDIANTE' => 'Estudiante tutor'];
    @endphp

    <button type="button" class="volver" onclick="history.length > 1 ? history.back() : location.assign('{{ route('estudiante.catalogo') }}')">← Volver</button>

    <section class="cab">
        <div class="banda"></div>
        <div class="cab-cuerpo">
            @if ($p['tiene_foto'])
                <img class="avatar" src="{{ route('perfil.foto', ['userId' => $p['id']]) }}" alt="Foto de {{ $p['nombre'] }}">
            @else
                <div class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($p['nombre'], 0, 1)) }}</div>
            @endif
            <div>
                <h2>{{ $p['nombre'] }}</h2>
                <div class="sub">{{ $tipos[$p['tipo']] ?? 'Tutor' }}@if ($p['facultad']) · {{ $p['facultad'] }}@endif</div>
                <div class="sub">
                    @if ($p['valoracion'] !== null)
                        <span class="estrellas">{{ str_repeat('★', (int) round($p['valoracion'])) }}{{ str_repeat('☆', 5 - (int) round($p['valoracion'])) }}</span>
                        {{ number_format($p['valoracion'], 1) }} · {{ $p['calificaciones'] }} calificación(es)
                    @else
                        Aún sin valoraciones
                    @endif
                    · {{ count($p['tutorias']) }} tutoría(s) disponible(s)
                </div>
            </div>
        </div>
    </section>

    <div class="layout">
        <div style="display: grid; gap: 18px;">
            <section class="panel">
                <h3>Sobre mí</h3>
                <p class="bio">{{ $p['biografia'] ?: 'El tutor aún no escribió su biografía.' }}</p>
            </section>

            <section>
                <h3 style="margin: 0 0 12px;">Tutorías disponibles</h3>
                @if (count($p['tutorias']) === 0)
                    <div class="panel sub">Este tutor no tiene tutorías publicadas en este momento.</div>
                @else
                    @include('livewire.cursos._tarjeta-estilos')
                    <div class="tt-lista">
                        @foreach ($p['tutorias'] as $t)
                            @include('livewire.cursos._tarjeta-tutoria', ['t' => $t, 'mostrarTutor' => false])
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <div style="display: grid; gap: 18px;">
            <section class="panel">
                <h3>Conocimientos</h3>
                @if (count($p['conocimientos']) > 0)
                    <div class="chips">@foreach ($p['conocimientos'] as $k) <span class="chip">{{ $k }}</span> @endforeach</div>
                @else
                    <div class="sub">Sin conocimientos registrados.</div>
                @endif
                @if (count($p['enlaces']) > 0)
                    <ul class="enlaces">
                        @foreach ($p['enlaces'] as $l)
                            <li><a href="{{ $l }}" target="_blank" rel="noopener noreferrer nofollow">{{ preg_replace('#^https?://(www\.)?#', '', $l) }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="aviso">📞 El número de contacto del tutor aparece en el detalle de la tutoría cuando te inscribes.</div>

            <section class="panel">
                <h3>Reseñas</h3>
                @forelse ($p['resenas'] as $r)
                    <div class="resena" wire:key="resena-{{ $r->id }}">
                        <div><span class="estrellas" aria-label="{{ $r->score }} de 5">{{ str_repeat('★', $r->score) }}{{ str_repeat('☆', 5 - $r->score) }}</span>
                            <span class="sub">· {{ $r->studentName }}</span></div>
                        <div class="sub" style="font-size: 12.5px;">{{ $r->courseTitle }} · {{ $r->date }}</div>
                        <p>{{ $r->comment }}</p>
                    </div>
                @empty
                    <p class="sub" style="margin: 0;">Este tutor aún no tiene reseñas.</p>
                @endforelse
            </section>
        </div>
    </div>
</div>
