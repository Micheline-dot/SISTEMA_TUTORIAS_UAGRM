<div class="gc">
    <style>
        .gc { max-width: 760px; display: grid; gap: 16px; }
        .gc .panel { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .gc form.fila, .gc .fila { display: flex; gap: 8px; align-items: flex-start; }
        .gc input { flex: 1; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; }
        .gc .btn { padding: 9px 14px; border: 0; border-radius: 8px; background: #174ea6; color: white; font: inherit; font-weight: 700; cursor: pointer; white-space: nowrap; }
        .gc .btn.sec { background: #e9eef5; color: #243047; }
        .gc .btn.peligro { background: #fdecea; color: #9b2c22; }
        .gc .btn.mini { padding: 6px 10px; font-size: 13px; font-weight: 600; }
        .gc table { width: 100%; border-collapse: collapse; font-size: 14.5px; }
        .gc th, .gc td { text-align: left; padding: 11px 10px; border-bottom: 1px solid #eef2f7; }
        .gc th { color: #65738a; font-weight: 600; }
        .gc .acciones { display: flex; gap: 6px; justify-content: flex-end; }
        .gc .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .gc .err { background: #fdecea; color: #9b2c22; border-radius: 8px; padding: 12px 14px; }
        .gc .error { color: #b42318; font-size: 13px; margin-top: 4px; }
        .gc .hint { color: #718096; font-size: 13.5px; margin: 0 0 12px; }
    </style>

    @if (session('ok')) <div class="ok">{{ session('ok') }}</div> @endif
    @if (session('error')) <div class="err">{{ session('error') }}</div> @endif

    <div class="panel">
        <p class="hint">Las categorías clasifican las tutorías y permiten filtrarlas en la búsqueda.</p>
        <form class="fila" wire:submit="crear">
            <div style="flex: 1;">
                <input type="text" wire:model="nueva" placeholder="Nueva categoría (ej. Estadística)" maxlength="100" aria-label="Nombre de la nueva categoría" style="width: 100%; box-sizing: border-box;">
                @error('nueva') <div class="error">{{ $message }}</div> @enderror
            </div>
            <button class="btn" type="submit">Agregar</button>
        </form>
    </div>

    <div class="panel" style="padding: 8px 12px;">
        <table>
            <thead><tr><th>Categoría</th><th>Tutorías</th><th></th></tr></thead>
            <tbody>
                @forelse ($categorias as $cat)
                    <tr wire:key="cat-{{ $cat->id }}">
                        @if ($editandoId === $cat->id)
                            <td colspan="2">
                                <form class="fila" wire:submit="guardarEdicion">
                                    <input type="text" wire:model="nombreEditado" maxlength="100" aria-label="Nuevo nombre">
                                    <button class="btn mini" type="submit">Guardar</button>
                                    <button class="btn sec mini" type="button" wire:click="cancelarEdicion">Cancelar</button>
                                </form>
                                @error('nombreEditado') <div class="error">{{ $message }}</div> @enderror
                            </td>
                            <td></td>
                        @else
                            <td><strong>{{ $cat->name }}</strong></td>
                            <td>{{ $cat->courseCount }}</td>
                            <td>
                                <div class="acciones">
                                    <button class="btn sec mini" wire:click="editar('{{ $cat->id }}', @js($cat->name))">Renombrar</button>
                                    @if ($cat->courseCount === 0)
                                        <button class="btn peligro mini" wire:click="eliminar('{{ $cat->id }}')"
                                                wire:confirm="¿Eliminar la categoría {{ $cat->name }}?">Eliminar</button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="3" style="color: #718096; text-align: center; padding: 24px;">Aún no hay categorías.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
