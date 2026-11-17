<div class="mt">
    <style>
        .mt { display: grid; gap: 18px; }
        .mt .barra { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .mt .filtros { display: flex; gap: 8px; flex-wrap: wrap; }
        .mt .chip { padding: 7px 13px; border-radius: 999px; border: 1px solid #c5d1e0; background: white; cursor: pointer; font: inherit; font-size: 13.5px; color: #243047; }
        .mt .chip.on { background: #174ea6; border-color: #174ea6; color: white; }
        .mt .btn { padding: 10px 16px; border: 0; border-radius: 8px; background: #174ea6; color: white; font: inherit; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
        .mt .btn:disabled { background: #8d9db3; cursor: not-allowed; }
        .mt .btn.sec { background: #e9eef5; color: #243047; }
        .mt .btn.mini { padding: 7px 12px; font-size: 13px; }
        .mt .lista { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
        .mt .card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); display: flex; flex-direction: column; gap: 10px; }
        .mt .card h3 { margin: 0; font-size: 17px; }
        .mt .meta { color: #65738a; font-size: 13.5px; line-height: 1.6; }
        .mt .estado { align-self: flex-start; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .mt .BORRADOR { background: #fff4d6; color: #8b6414; }
        .mt .PUBLICADO { background: #e7f6ec; color: #1e6b3a; }
        .mt .CANCELADO { background: #fdecea; color: #9b2c22; }
        .mt .FINALIZADO { background: #eceff3; color: #4a5568; }
        .mt .acciones { display: flex; gap: 8px; flex-wrap: wrap; margin-top: auto; }
        .mt .warn { background: #fff4e5; color: #8a5300; border-radius: 10px; padding: 14px 16px; line-height: 1.5; }
        .mt .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .mt .vacio { background: white; border-radius: 12px; padding: 30px; text-align: center; color: #718096; }
        .mt .fondo { position: fixed; inset: 0; background: rgba(15, 30, 55, .45); display: grid; place-items: center; padding: 16px; z-index: 50; }
        .mt .modal { background: white; border-radius: 14px; padding: 24px; width: min(100%, 480px); }
        .mt .modal h2 { margin: 0 0 6px; font-size: 19px; }
        .mt .modal label { display: block; font-weight: 600; font-size: 14px; margin: 12px 0 5px; }
        .mt .modal input, .mt .modal select { width: 100%; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; box-sizing: border-box; background: white; }
        .mt .modal .pie { display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px; }
        .mt .error { color: #b42318; font-size: 13px; margin-top: 4px; }
        .mt .hint { color: #718096; font-size: 13.5px; margin: 0; line-height: 1.5; }
    </style>

    @if (session('ok')) <div class="ok">{{ session('ok') }}</div> @endif

    @unless ($perfilCompleto)
        <div class="warn">
            <strong>Completa tu perfil académico para crear tutorías.</strong>
            Los estudiantes verán tu biografía y conocimientos junto a cada tutoría.
            <a href="{{ route('tutor.perfil-academico') }}">Ir a mi perfil académico →</a>
        </div>
    @endunless

    <div class="barra">
        <div class="filtros">
            @foreach (['' => 'Todas', 'BORRADOR' => 'Borradores', 'PUBLICADO' => 'Publicadas', 'FINALIZADO' => 'Finalizadas', 'CANCELADO' => 'Canceladas'] as $valor => $etiqueta)
                <button class="chip {{ $filtro === $valor ? 'on' : '' }}" wire:click="$set('filtro', '{{ $valor }}')">{{ $etiqueta }}</button>
            @endforeach
        </div>
        <button class="btn" wire:click="nuevaTutoria" @disabled(! $perfilCompleto)>+ Nueva tutoría</button>
    </div>

    @if (count($tutorias) === 0)
        <div class="vacio">{{ $filtro === '' ? 'Todavía no creaste tutorías.' : 'No hay tutorías con ese estado.' }}</div>
    @else
        <div class="lista">
            @foreach ($tutorias as $t)
                <article class="card" wire:key="tutoria-{{ $t->id }}">
                    <span class="estado {{ $t->status->value }}">{{ ucfirst(mb_strtolower($t->status->value)) }}</span>
                    <h3>{{ $t->title ?: 'Sin título' }}</h3>
                    <div class="meta">
                        {{ $t->categoryName ?? 'Sin categoría' }}
                        @if ($t->modality) · {{ ucfirst(mb_strtolower($t->modality->value)) }} @endif
                        <br>
                        @if ($t->startDate)
                            {{ \Illuminate\Support\Carbon::parse($t->startDate)->format('d/m/Y') }}
                            @if ($t->endDate) – {{ \Illuminate\Support\Carbon::parse($t->endDate)->format('d/m/Y') }} @endif
                        @else
                            Sin fechas
                        @endif
                        @if ($t->totalSeats)
                            <br>{{ $t->activeEnrollments }} / {{ $t->totalSeats }} inscritos
                        @endif
                    </div>
                    <div class="acciones">
                        <a class="btn mini" href="{{ route('tutor.tutoria', $t->id) }}" wire:navigate>Gestionar</a>
                        @if (in_array($t->status->value, ['PUBLICADO', 'FINALIZADO'], true))
                            <a class="btn sec mini" href="{{ route('tutor.asistencia', $t->id) }}">Asistencia</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @if ($mostrarNueva)
        <div class="fondo" wire:click.self="cerrarNueva">
            <form class="modal" wire:submit="crear">
                <h2>Nueva tutoría</h2>
                <p class="hint">Se crea como borrador. Después completas descripción, fechas, cupos y temario.</p>

                <label for="titulo">Título</label>
                <input id="titulo" type="text" wire:model="titulo" maxlength="150" placeholder="Ej. Repaso de Cálculo I">
                @error('titulo') <div class="error">{{ $message }}</div> @enderror

                <label for="categoria">Categoría</label>
                <select id="categoria" wire:model="categoria">
                    <option value="">Selecciona una categoría</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('categoria') <div class="error">{{ $message }}</div> @enderror

                <div class="pie">
                    <button type="button" class="btn sec" wire:click="cerrarNueva">Cancelar</button>
                    <button type="submit" class="btn" wire:loading.attr="disabled">Crear borrador</button>
                </div>
            </form>
        </div>
    @endif
</div>
