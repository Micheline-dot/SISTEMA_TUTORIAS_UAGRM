<div class="ga">
    <style>
        .ga { display: grid; gap: 16px; max-width: 1000px; }
        .ga .volver { color: #174ea6; text-decoration: none; font-weight: 600; font-size: 14px; }
        .ga .panel { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .ga h2 { margin: 0 0 4px; font-size: 18px; }
        .ga .hint { color: #718096; font-size: 13.5px; margin: 0 0 12px; line-height: 1.5; }
        .ga .resumen { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .ga .cifra { background: #f4f7fb; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #52657e; }
        .ga .cifra strong { display: block; font-size: 20px; color: #172b4d; }
        .ga .tabla { overflow-x: auto; }
        .ga table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ga th, .ga td { text-align: left; padding: 11px 10px; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
        .ga th { color: #65738a; font-weight: 600; }
        .ga .sub { color: #8492a6; font-size: 12.5px; }
        .ga .estado { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .ga .e-activa { background: #eef4ff; color: #1f4b8f; }
        .ga .e-asistio { background: #fff4d6; color: #8b6414; }
        .ga .e-confirmada { background: #e7f6ec; color: #1e6b3a; }
        .ga .e-cancelada, .ga .e-eliminada { background: #fdecea; color: #9b2c22; }
        .ga .acciones { display: flex; gap: 6px; flex-wrap: wrap; }
        .ga .btn { padding: 7px 12px; border: 0; border-radius: 8px; background: #16804a; color: white; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer; white-space: nowrap; }
        .ga .btn.sec { background: #e9eef5; color: #243047; }
        .ga .btn.peligro { background: #fdecea; color: #9b2c22; }
        .ga .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .ga .err { background: #fdecea; color: #9b2c22; border-radius: 8px; padding: 12px 14px; }
        .ga .vacio { color: #718096; text-align: center; padding: 22px; }
        .ga .fondo { position: fixed; inset: 0; background: rgba(15, 30, 55, .45); display: grid; place-items: center; padding: 16px; z-index: 50; }
        .ga .modal { background: white; border-radius: 14px; padding: 24px; width: min(100%, 460px); }
        .ga .modal h2 { margin-bottom: 6px; }
        .ga .modal textarea { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; }
        .ga .modal .pie { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
        .ga .error { color: #b42318; font-size: 13px; margin-top: 4px; }
    </style>

    @php
        $etiquetas = [
            'activa' => 'Inscrito',
            'asistio' => 'Asistió · falta confirmar',
            'confirmada' => 'Participación confirmada',
            'cancelada' => 'Canceló',
            'eliminada' => 'Retirado',
        ];
        $fmt = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('d/m/Y') : '—';
        $porEstado = collect($estudiantes)->countBy(fn ($e) => $e['estado_participacion']);
    @endphp

    <a class="volver" href="{{ route('tutor.tutoria', $curso->id) }}" wire:navigate>← Volver a la tutoría</a>

    @if (session()->has('success')) <div class="ok">{{ session('success') }}</div> @endif
    @error('general') <div class="err">{{ $message }}</div> @enderror

    <section class="panel">
        <h2>{{ $curso->title }}</h2>
        <p class="hint">
            1) Registra la asistencia de cada estudiante · 2) el estudiante confirma su participación.
            Puedes retirar a quien todavía no tiene la asistencia registrada.
        </p>
        <div class="resumen">
            <div class="cifra"><strong>{{ count($estudiantes) }}{{ $curso->totalSeats ? ' / '.$curso->totalSeats : '' }}</strong>inscritos</div>
            <div class="cifra"><strong>{{ $porEstado['activa'] ?? 0 }}</strong>sin asistencia</div>
            <div class="cifra"><strong>{{ $porEstado['asistio'] ?? 0 }}</strong>por confirmar</div>
            <div class="cifra"><strong>{{ $porEstado['confirmada'] ?? 0 }}</strong>confirmados</div>
        </div>
    </section>

    <section class="panel tabla">
        <h2>Estudiantes inscritos</h2>
        @if (count($estudiantes) === 0)
            <div class="vacio">Todavía no hay estudiantes inscritos.</div>
        @else
            <table>
                <thead><tr><th>Estudiante</th><th>Inscrito</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    @foreach ($estudiantes as $e)
                        @php $p = $e['estado_participacion']; @endphp
                        <tr wire:key="inscrito-{{ $e['id_inscripcion'] }}">
                            <td><strong>{{ $e['nombre_completo'] }}</strong><div class="sub">{{ $e['correo'] }}</div></td>
                            <td>{{ $fmt($e['fecha_inscripcion']) }}</td>
                            <td><span class="estado e-{{ $p }}">{{ $etiquetas[$p] ?? $p }}</span></td>
                            <td>
                                @if ($puedeGestionar && $p === 'activa')
                                    <div class="acciones">
                                        <button class="btn" wire:click="verificar('{{ $e['id_estudiante'] }}')"
                                                wire:confirm="¿Confirmas que {{ $e['nombre_completo'] }} asistió a la tutoría?">Registrar asistencia</button>
                                        @if ($curso->status->value === 'PUBLICADO')
                                            <button class="btn peligro" wire:click="abrirRetirar('{{ $e['id_estudiante'] }}')">Retirar</button>
                                        @endif
                                    </div>
                                @else
                                    <span class="sub">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    @if (count($historial) > 0)
        <section class="panel tabla">
            <h2>Historial</h2>
            <p class="hint">Inscripciones canceladas por el estudiante o retiradas por ti.</p>
            <table>
                <thead><tr><th>Estudiante</th><th>Estado</th><th>Motivo</th></tr></thead>
                <tbody>
                    @foreach ($historial as $h)
                        <tr wire:key="hist-{{ $h['id_inscripcion'] }}">
                            <td>{{ $h['nombre_completo'] }}<div class="sub">{{ $h['correo'] }}</div></td>
                            <td><span class="estado e-{{ $h['estado_participacion'] }}">{{ $etiquetas[$h['estado_participacion']] ?? $h['estado_participacion'] }}</span></td>
                            <td>{{ $h['motivo_cancelacion'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    @if ($retirando)
        <div class="fondo" wire:click.self="cerrarRetirar">
            <form class="modal" wire:submit="retirar">
                <h2>Retirar estudiante</h2>
                <p class="hint">{{ $retirando['nombre_completo'] }} dejará de estar inscrito, se liberará su cupo y recibirá un aviso con el motivo.</p>
                <label for="motivo" style="display:block;font-weight:600;font-size:14px;margin-bottom:5px;">Motivo</label>
                <textarea id="motivo" rows="3" wire:model="motivo" maxlength="500" placeholder="Ej. No se presentó a las primeras sesiones"></textarea>
                @error('motivo') <div class="error">{{ $message }}</div> @enderror
                <div class="pie">
                    <button type="button" class="btn sec" wire:click="cerrarRetirar">Volver</button>
                    <button type="submit" class="btn peligro" wire:loading.attr="disabled">Retirar</button>
                </div>
            </form>
        </div>
    @endif
</div>
