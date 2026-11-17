<div class="mi">
    <style>
        .mi { display: grid; gap: 16px; max-width: 980px; }
        .mi .tabs { display: flex; gap: 8px; flex-wrap: wrap; }
        .mi .tab { padding: 8px 14px; border-radius: 999px; border: 1px solid #c5d1e0; background: white; cursor: pointer; font: inherit; font-size: 14px; color: #243047; }
        .mi .tab.on { background: #174ea6; border-color: #174ea6; color: white; }
        .mi .lista { display: grid; gap: 12px; }
        .mi .card { background: white; border-radius: 12px; padding: 18px 20px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px 18px; align-items: start; }
        .mi .card h3 { margin: 0 0 4px; font-size: 16.5px; }
        .mi .card h3 a { color: #172b4d; text-decoration: none; }
        .mi .card h3 a:hover { color: #174ea6; }
        .mi .meta { color: #65738a; font-size: 13.5px; line-height: 1.6; }
        .mi .estado { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; margin-bottom: 6px; }
        .mi .e-activa { background: #eef4ff; color: #1f4b8f; }
        .mi .e-asistio { background: #fff4d6; color: #8b6414; }
        .mi .e-confirmada { background: #e7f6ec; color: #1e6b3a; }
        .mi .e-cancelada, .mi .e-eliminada { background: #fdecea; color: #9b2c22; }
        .mi .aviso { grid-column: 1 / -1; background: #fff9ec; color: #7a5410; border-radius: 8px; padding: 10px 12px; font-size: 13.5px; }
        .mi .motivo { grid-column: 1 / -1; color: #9b2c22; font-size: 13.5px; }
        .mi .acciones { display: flex; flex-direction: column; gap: 8px; align-items: stretch; min-width: 170px; }
        .mi .btn { padding: 9px 14px; border: 0; border-radius: 8px; background: #174ea6; color: white; font: inherit; font-weight: 700; font-size: 14px; cursor: pointer; text-align: center; text-decoration: none; }
        .mi .btn.ok-btn { background: #16804a; }
        .mi .btn.sec { background: #e9eef5; color: #243047; }
        .mi .btn.peligro { background: #fdecea; color: #9b2c22; }
        .mi .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .mi .err { background: #fdecea; color: #9b2c22; border-radius: 8px; padding: 12px 14px; }
        .mi .vacio { background: white; border-radius: 12px; padding: 30px; text-align: center; color: #718096; }
        .mi .vacio a { color: #174ea6; font-weight: 600; }
        .mi .fondo { position: fixed; inset: 0; background: rgba(15, 30, 55, .45); display: grid; place-items: center; padding: 16px; z-index: 50; }
        .mi .modal { background: white; border-radius: 14px; padding: 24px; width: min(100%, 460px); }
        .mi .modal h2 { margin: 0 0 6px; font-size: 19px; }
        .mi .modal p { color: #65738a; font-size: 14px; margin: 0 0 10px; line-height: 1.5; }
        .mi .modal textarea { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; }
        .mi .modal .pie { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
        .mi .error { color: #b42318; font-size: 13px; margin-top: 4px; }
        @media (max-width: 640px) { .mi .card { grid-template-columns: 1fr; } .mi .acciones { min-width: 0; } }
    </style>

    @php
        $etiquetas = [
            'activa' => 'Inscrito',
            'asistio' => 'Asistencia registrada',
            'confirmada' => 'Participación confirmada',
            'cancelada' => 'Cancelada',
            'eliminada' => 'Retirado por el tutor',
        ];
        $fmt = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('d/m/Y') : null;
    @endphp

    @if (session()->has('success')) <div class="ok">{{ session('success') }}</div> @endif
    @error('general') <div class="err">{{ $message }}</div> @enderror

    <div class="tabs">
        <button class="tab {{ $vista === 'activas' ? 'on' : '' }}" wire:click="$set('vista', 'activas')">Activas ({{ $totalActivas }})</button>
        <button class="tab {{ $vista === 'historial' ? 'on' : '' }}" wire:click="$set('vista', 'historial')">Historial ({{ $totalHistorial }})</button>
    </div>

    @if (count($inscripciones) === 0)
        <div class="vacio">
            @if ($vista === 'activas')
                No tienes inscripciones activas. <a href="{{ route('estudiante.catalogo') }}">Busca una tutoría</a>.
            @else
                Aún no tienes inscripciones canceladas o finalizadas.
            @endif
        </div>
    @else
        <div class="lista">
            @foreach ($inscripciones as $i)
                @php
                    $p = $i['estado_participacion'] ?? 'activa';
                    $curso = $i['curso'];
                    $cursoActivo = ($curso['estado'] ?? null) === 'PUBLICADO';
                @endphp
                <article class="card" wire:key="inscripcion-{{ $i['id_inscripcion'] }}">
                    <div>
                        <span class="estado e-{{ $p }}">{{ $etiquetas[$p] ?? $p }}</span>
                        <h3><a href="{{ route('estudiante.tutoria', $i['id_curso']) }}" wire:navigate>{{ $curso['titulo'] ?? 'Tutoría sin título' }}</a></h3>
                        <div class="meta">
                            Tutor: <strong>{{ $curso['tutor']['nombre_completo'] ?? '—' }}</strong>
                            @if ($curso['modalidad'] ?? null) · {{ ucfirst(mb_strtolower($curso['modalidad'])) }} @endif
                            @if ($fmt($curso['fecha_inicio'] ?? null))
                                <br>{{ $fmt($curso['fecha_inicio']) }} – {{ $fmt($curso['fecha_finalizacion'] ?? null) }}
                            @endif
                            <br>Inscrito el {{ $fmt($i['fecha_inscripcion']) }}
                            @if (! $cursoActivo && ($curso['estado'] ?? null)) · Tutoría {{ mb_strtolower($curso['estado']) }} @endif
                        </div>
                    </div>

                    <div class="acciones">
                        <a class="btn sec" href="{{ route('estudiante.tutoria', $i['id_curso']) }}" wire:navigate>Ver tutoría</a>
                        @if ($i['estado'] === 'INSCRITO' && $p === 'asistio')
                            <button class="btn ok-btn" wire:click="confirmarParticipacion('{{ $i['id_curso'] }}')"
                                    wire:confirm="¿Confirmas que participaste en esta tutoría?">Confirmar participación</button>
                        @endif
                        @if ($i['estado'] === 'INSCRITO' && $p === 'activa' && $cursoActivo)
                            <button class="btn peligro" wire:click="abrirCancelar('{{ $i['id_curso'] }}')">Cancelar inscripción</button>
                        @endif
                    </div>

                    @if ($i['estado'] === 'INSCRITO' && $p === 'asistio')
                        <div class="aviso">El tutor registró tu asistencia. Confírmala para completar tu participación y poder calificar al tutor.</div>
                    @endif
                    @if ($i['estado'] !== 'INSCRITO' && $i['motivo_cancelacion'])
                        <div class="motivo">Motivo: {{ $i['motivo_cancelacion'] }}</div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    @if ($cancelandoCurso)
        <div class="fondo" wire:click.self="cerrarCancelar">
            <form class="modal" wire:submit="cancelarInscripcion">
                <h2>Cancelar inscripción</h2>
                <p>Se liberará tu cupo y el tutor recibirá un aviso con el motivo.</p>
                <label for="motivo" style="display:block;font-weight:600;font-size:14px;margin-bottom:5px;">Motivo</label>
                <textarea id="motivo" rows="3" wire:model="motivo" maxlength="500" placeholder="Ej. Se cruza con mi horario de clases"></textarea>
                @error('motivo') <div class="error">{{ $message }}</div> @enderror
                <div class="pie">
                    <button type="button" class="btn sec" wire:click="cerrarCancelar">Volver</button>
                    <button type="submit" class="btn peligro" wire:loading.attr="disabled">Cancelar inscripción</button>
                </div>
            </form>
        </div>
    @endif
</div>
