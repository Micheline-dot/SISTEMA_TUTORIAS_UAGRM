<div class="gt">
    <style>
        .gt { display: grid; gap: 18px; max-width: 980px; }
        .gt .panel { background: white; border-radius: 12px; padding: 22px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .gt h2 { margin: 0 0 4px; font-size: 18px; }
        .gt .hint { color: #718096; font-size: 13.5px; margin: 0 0 12px; line-height: 1.5; }
        .gt label { display: block; font-weight: 600; font-size: 14px; margin: 12px 0 5px; }
        .gt input, .gt select, .gt textarea { width: 100%; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; box-sizing: border-box; background: white; }
        .gt input[disabled], .gt select[disabled], .gt textarea[disabled] { background: #f1f4f8; color: #4a5568; }
        .gt .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 16px; }
        .gt .grid3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0 16px; }
        .gt .btn { padding: 10px 16px; border: 0; border-radius: 8px; background: #174ea6; color: white; font: inherit; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; white-space: nowrap; }
        .gt .btn:disabled { background: #8d9db3; cursor: not-allowed; }
        .gt .btn.sec { background: #e9eef5; color: #243047; }
        .gt .btn.ok-btn { background: #16804a; }
        .gt .btn.peligro { background: #fdecea; color: #9b2c22; }
        .gt .btn.mini { padding: 6px 10px; font-size: 13px; font-weight: 600; }
        .gt .cabecera { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .gt .estado { padding: 5px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 700; }
        .gt .BORRADOR { background: #fff4d6; color: #8b6414; }
        .gt .PUBLICADO { background: #e7f6ec; color: #1e6b3a; }
        .gt .CANCELADO { background: #fdecea; color: #9b2c22; }
        .gt .FINALIZADO { background: #eceff3; color: #4a5568; }
        .gt .acciones { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; }
        .gt .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .gt .err { background: #fdecea; color: #9b2c22; border-radius: 8px; padding: 12px 14px; }
        .gt .warn { background: #fff4e5; color: #8a5300; border-radius: 8px; padding: 12px 14px; font-size: 14px; line-height: 1.6; }
        .gt .warn ul { margin: 6px 0 0; padding-left: 20px; }
        .gt .listo { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; font-size: 14px; }
        .gt .error { color: #b42318; font-size: 13px; margin-top: 4px; }
        .gt .tema { border: 1px solid #e3e9f1; border-radius: 10px; padding: 14px; margin-top: 10px; }
        .gt .tema.hecho { background: #f6fbf8; }
        .gt .tema-cab { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; flex-wrap: wrap; }
        .gt .tema-nombre { font-weight: 700; }
        .gt .tema.hecho .tema-nombre { text-decoration: line-through; color: #4a5568; }
        .gt .sub { color: #8492a6; font-size: 13px; }
        .gt .recursos { margin: 10px 0 0; padding: 0; list-style: none; display: grid; gap: 6px; }
        .gt .recursos li { display: flex; justify-content: space-between; gap: 10px; align-items: center; background: #f8fafc; border-radius: 8px; padding: 8px 10px; font-size: 14px; }
        .gt .recursos a { color: #174ea6; overflow-wrap: anywhere; }
        .gt .fila { display: flex; gap: 8px; align-items: flex-start; flex-wrap: wrap; }
        .gt .fila > * { flex: 1; min-width: 150px; }
        .gt .fila > .btn { flex: 0 0 auto; min-width: 0; }
        .gt .fondo { position: fixed; inset: 0; background: rgba(15, 30, 55, .45); display: grid; place-items: center; padding: 16px; z-index: 50; }
        .gt .modal { background: white; border-radius: 14px; padding: 24px; width: min(100%, 480px); }
        .gt .volver { color: #174ea6; text-decoration: none; font-weight: 600; font-size: 14px; }
        @media (max-width: 700px) { .gt .grid, .gt .grid3 { grid-template-columns: 1fr; } }
    </style>

    @php $estado = $curso->status->value; $esBorrador = $estado === 'BORRADOR'; $esPublicado = $estado === 'PUBLICADO'; @endphp

    <a class="volver" href="{{ route('tutor.tutorias') }}" wire:navigate>← Mis tutorías</a>

    @if (session('ok')) <div class="ok">{{ session('ok') }}</div> @endif
    @error('general') <div class="err">{{ $message }}</div> @enderror

    {{-- Cabecera y acciones del ciclo de vida --}}
    <section class="panel">
        <div class="cabecera">
            <div>
                <h2>{{ $curso->title ?: 'Sin título' }}</h2>
                <div class="sub">
                    {{ $curso->categoryName ?? 'Sin categoría' }}
                    @if ($curso->totalSeats) · {{ $curso->activeEnrollments }} / {{ $curso->totalSeats }} inscritos @endif
                </div>
            </div>
            <span class="estado {{ $estado }}">{{ ucfirst(mb_strtolower($estado)) }}</span>
        </div>

        @if ($esBorrador)
            @if (count($faltantes) > 0)
                <div class="warn" style="margin-top: 14px;">
                    <strong>Para publicar falta:</strong>
                    <ul>@foreach ($faltantes as $f) <li>{{ $f }}</li> @endforeach</ul>
                </div>
            @else
                <div class="listo" style="margin-top: 14px;">Todo listo: ya puedes publicar esta tutoría.</div>
            @endif
            <div class="acciones">
                <button class="btn ok-btn" wire:click="publicarTutoria" @disabled(count($faltantes) > 0)
                        wire:confirm="Al publicarla, la tutoría será visible para los estudiantes y podrán inscribirse. ¿Publicar ahora?">
                    Publicar tutoría
                </button>
                <button class="btn peligro" wire:click="eliminarBorrador"
                        wire:confirm="Se eliminará el borrador con su temario. ¿Continuar?">Eliminar borrador</button>
            </div>
        @elseif ($esPublicado)
            <div class="acciones">
                <a class="btn sec" href="{{ route('tutor.asistencia', $curso->id) }}">Ver inscritos y asistencia</a>
                <button class="btn sec" wire:click="finalizarTutoria" @disabled($curso->startDate && $curso->startDate > $hoy)
                        wire:confirm="La tutoría pasará a finalizada y ya no recibirá inscripciones. ¿Continuar?">Marcar como finalizada</button>
                <button class="btn peligro" wire:click="abrirCancelar">Cancelar tutoría</button>
            </div>
        @elseif ($estado === 'CANCELADO')
            <div class="err" style="margin-top: 14px;">Cancelada. Motivo: {{ $curso->cancellationReason ?: '—' }}</div>
        @else
            <div class="acciones">
                <a class="btn sec" href="{{ route('tutor.asistencia', $curso->id) }}">Ver inscritos y asistencia</a>
            </div>
        @endif
    </section>

    {{-- Datos --}}
    <section class="panel">
        <h2>Datos de la tutoría</h2>

        @if ($esBorrador)
            <p class="hint">Puedes guardar con datos incompletos; todos se exigen al publicar.</p>
            <form wire:submit="guardarBorrador">
                <label for="titulo">Título</label>
                <input id="titulo" type="text" wire:model="titulo" maxlength="150">
                @error('titulo') <div class="error">{{ $message }}</div> @enderror

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" rows="4" wire:model="descripcion" maxlength="5000" placeholder="¿Qué aprenderán los estudiantes? ¿A quién está dirigida?"></textarea>
                @error('descripcion') <div class="error">{{ $message }}</div> @enderror

                <div class="grid3">
                    <div>
                        <label for="categoria">Categoría</label>
                        <select id="categoria" wire:model="categoria">
                            <option value="">Selecciona</option>
                            @foreach ($categorias as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="modalidad">Modalidad</label>
                        <select id="modalidad" wire:model="modalidad">
                            <option value="">Selecciona</option>
                            <option value="PRESENCIAL">Presencial</option>
                            <option value="VIRTUAL">Virtual</option>
                            <option value="HIBRIDA">Híbrida</option>
                        </select>
                    </div>
                    <div>
                        <label for="gratuito">Costo</label>
                        <select id="gratuito" wire:model="gratuito">
                            <option value="1">Gratuita</option>
                            <option value="0">De pago (se coordina fuera del sistema)</option>
                        </select>
                    </div>
                </div>

                <div class="grid3">
                    <div>
                        <label for="cupos">Cupos</label>
                        <input id="cupos" type="number" min="1" max="1000" wire:model="cupos">
                        @error('cupos') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="fechaInicio">Fecha de inicio</label>
                        <input id="fechaInicio" type="date" min="{{ $hoy }}" wire:model="fechaInicio">
                        @error('fechaInicio') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="fechaFin">Fecha de finalización</label>
                        <input id="fechaFin" type="date" wire:model="fechaFin">
                        @error('fechaFin') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="acciones"><button class="btn" type="submit" wire:loading.attr="disabled">Guardar datos</button></div>
            </form>
        @else
            <div class="grid">
                <div><label>Descripción</label><textarea rows="3" disabled>{{ $curso->description }}</textarea></div>
                <div class="grid" style="grid-template-columns: 1fr 1fr;">
                    <div><label>Modalidad</label><input disabled value="{{ $curso->modality ? ucfirst(mb_strtolower($curso->modality->value)) : '—' }}"></div>
                    <div><label>Costo</label><input disabled value="{{ $curso->isFree ? 'Gratuita' : 'De pago' }}"></div>
                </div>
            </div>

            @if ($esPublicado)
                <form wire:submit="guardarPublicado">
                    <p class="hint" style="margin-top: 14px;">En una tutoría publicada puedes ajustar cupos y fechas. Si cambian las fechas, se avisa a los inscritos.</p>
                    <div class="grid3">
                        <div>
                            <label for="cupos">Cupos (mínimo {{ max(1, $curso->activeEnrollments) }})</label>
                            <input id="cupos" type="number" min="{{ max(1, $curso->activeEnrollments) }}" max="1000" wire:model="cupos">
                            @error('cupos') <div class="error">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="fechaInicio">Fecha de inicio</label>
                            <input id="fechaInicio" type="date" wire:model="fechaInicio">
                            @error('fechaInicio') <div class="error">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="fechaFin">Fecha de finalización</label>
                            <input id="fechaFin" type="date" wire:model="fechaFin">
                            @error('fechaFin') <div class="error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="acciones"><button class="btn" type="submit" wire:loading.attr="disabled">Guardar cambios</button></div>
                </form>
            @else
                <div class="grid3">
                    <div><label>Cupos</label><input disabled value="{{ $curso->totalSeats }}"></div>
                    <div><label>Inicio</label><input disabled value="{{ $curso->startDate ? \Illuminate\Support\Carbon::parse($curso->startDate)->format('d/m/Y') : '—' }}"></div>
                    <div><label>Fin</label><input disabled value="{{ $curso->endDate ? \Illuminate\Support\Carbon::parse($curso->endDate)->format('d/m/Y') : '—' }}"></div>
                </div>
            @endif
        @endif
    </section>

    {{-- Temario y recursos --}}
    <section class="panel">
        <h2>Temario</h2>
        <p class="hint">Organiza los temas y comparte materiales mediante enlaces (Drive, YouTube, documentos…).</p>
        @error('temario') <div class="err">{{ $message }}</div> @enderror

        @if ($editableTemario)
            <form class="fila" wire:submit="agregarTema">
                <div>
                    <input type="text" wire:model="nuevoTema" maxlength="150" placeholder="Nuevo tema (ej. Límites y continuidad)" aria-label="Nombre del tema">
                    @error('nuevoTema') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div style="max-width: 190px;">
                    <input type="date" wire:model="nuevoTemaFecha" aria-label="Fecha del tema" min="{{ $curso->startDate }}" max="{{ $curso->endDate }}">
                    @error('nuevoTemaFecha') <div class="error">{{ $message }}</div> @enderror
                </div>
                <button class="btn" type="submit">Agregar tema</button>
            </form>
        @endif

        @forelse ($temas as $n => $tema)
            <div class="tema {{ $tema->isCompleted() ? 'hecho' : '' }}" wire:key="tema-{{ $tema->id }}">
                @if ($editTemaId === $tema->id)
                    <div class="fila">
                        <div><input type="text" wire:model="editTemaNombre" maxlength="150" aria-label="Nombre del tema">
                            @error('editTemaNombre') <div class="error">{{ $message }}</div> @enderror</div>
                        <div style="max-width: 190px;"><input type="date" wire:model="editTemaFecha" aria-label="Fecha del tema"></div>
                        <button class="btn mini" wire:click="guardarTema('{{ $tema->status }}')">Guardar</button>
                        <button class="btn sec mini" wire:click="cancelarEdicionTema">Cancelar</button>
                    </div>
                @else
                    <div class="tema-cab">
                        <div>
                            <div class="tema-nombre">{{ $n + 1 }}. {{ $tema->name }}</div>
                            <div class="sub">
                                {{ $tema->date ? \Illuminate\Support\Carbon::parse($tema->date)->format('d/m/Y') : 'Sin fecha' }}
                                · {{ $tema->isCompleted() ? 'Completado' : 'Pendiente' }}
                            </div>
                        </div>
                        @if ($editableTemario)
                            <div class="fila" style="flex: 0 0 auto;">
                                @if ($esPublicado)
                                    <button class="btn sec mini" wire:click="cambiarEstadoTema('{{ $tema->id }}', @js($tema->name), @js($tema->date), '{{ $tema->isCompleted() ? 'PENDIENTE' : 'COMPLETADO' }}')">
                                        {{ $tema->isCompleted() ? 'Marcar pendiente' : 'Marcar completado' }}
                                    </button>
                                @endif
                                <button class="btn sec mini" wire:click="editarTema('{{ $tema->id }}', @js($tema->name), @js($tema->date))">Editar</button>
                                <button class="btn sec mini" wire:click="abrirRecurso('{{ $tema->id }}')">+ Recurso</button>
                                <button class="btn peligro mini" wire:click="eliminarTema('{{ $tema->id }}')"
                                        wire:confirm="¿Eliminar el tema y sus recursos?">Eliminar</button>
                            </div>
                        @endif
                    </div>
                @endif

                @if (count($tema->resources) > 0)
                    <ul class="recursos">
                        @foreach ($tema->resources as $r)
                            <li wire:key="recurso-{{ $r->id }}">
                                <a href="{{ $r->url }}" target="_blank" rel="noopener noreferrer">{{ $r->name }}</a>
                                @if ($editableTemario)
                                    <button class="btn peligro mini" wire:click="eliminarRecurso('{{ $r->id }}')" aria-label="Quitar recurso {{ $r->name }}">Quitar</button>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($recursoTemaId === $tema->id)
                    <form class="fila" style="margin-top: 10px;" wire:submit="agregarRecurso">
                        <div><input type="text" wire:model="recursoNombre" maxlength="150" placeholder="Nombre (ej. Guía de ejercicios)" aria-label="Nombre del recurso">
                            @error('recursoNombre') <div class="error">{{ $message }}</div> @enderror</div>
                        <div><input type="url" wire:model="recursoEnlace" maxlength="255" placeholder="https://…" aria-label="Enlace del recurso">
                            @error('recursoEnlace') <div class="error">{{ $message }}</div> @enderror</div>
                        <button class="btn mini" type="submit">Agregar</button>
                        <button class="btn sec mini" type="button" wire:click="cerrarRecurso">Cancelar</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="hint" style="margin-top: 12px;">El temario está vacío. Agrega al menos un tema para poder publicar.</p>
        @endforelse
    </section>

    @if ($mostrarCancelar)
        <div class="fondo" wire:click.self="cerrarCancelar">
            <form class="modal" wire:submit="cancelarTutoria">
                <h2>Cancelar tutoría</h2>
                <p class="hint">Se cancelarán las {{ $curso->activeEnrollments }} inscripción(es) activa(s) y se avisará a los estudiantes con el motivo.</p>
                <label for="motivo">Motivo</label>
                <textarea id="motivo" rows="3" wire:model="motivo" maxlength="500"></textarea>
                @error('motivo') <div class="error">{{ $message }}</div> @enderror
                <div class="acciones" style="justify-content: flex-end;">
                    <button type="button" class="btn sec" wire:click="cerrarCancelar">Volver</button>
                    <button type="submit" class="btn peligro">Cancelar tutoría</button>
                </div>
            </form>
        </div>
    @endif
</div>
