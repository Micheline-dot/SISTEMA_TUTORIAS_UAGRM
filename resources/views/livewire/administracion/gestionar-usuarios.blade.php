<div class="gu">
    <style>
        .gu { display: grid; gap: 16px; }
        .gu .barra { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .gu .barra input, .gu .barra select { padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; background: white; }
        .gu .barra input { flex: 1; min-width: 200px; }
        .gu .btn { padding: 9px 16px; border: 0; border-radius: 8px; background: #174ea6; color: white; font: inherit; font-weight: 700; cursor: pointer; white-space: nowrap; }
        .gu .btn.sec { background: #e9eef5; color: #243047; }
        .gu .btn.peligro { background: #fdecea; color: #9b2c22; }
        .gu .btn.mini { padding: 6px 10px; font-size: 13px; font-weight: 600; }
        .gu .tabla { background: white; border-radius: 12px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); overflow-x: auto; }
        .gu table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .gu th, .gu td { text-align: left; padding: 12px 14px; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
        .gu th { color: #65738a; font-weight: 600; background: #f8fafc; }
        .gu .sub { color: #8492a6; font-size: 12.5px; }
        .gu .rol { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; margin: 1px 2px 1px 0; }
        .gu .rol.ESTUDIANTE { background: #e7f2fb; color: #1d5a86; }
        .gu .rol.TUTOR { background: #efe9fb; color: #5b3aa0; }
        .gu .rol.ADMINISTRADOR { background: #fff1e0; color: #8a5300; }
        .gu .estado { font-weight: 700; font-size: 13px; }
        .gu .estado.on { color: #1e6b3a; }
        .gu .estado.off { color: #9b2c22; }
        .gu .acciones { display: flex; gap: 6px; flex-wrap: wrap; }
        .gu .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .gu .err { background: #fdecea; color: #9b2c22; border-radius: 8px; padding: 12px 14px; }
        .gu .pag { display: flex; gap: 6px; align-items: center; justify-content: flex-end; color: #65738a; font-size: 14px; }
        .gu .fondo { position: fixed; inset: 0; background: rgba(15, 30, 55, .45); display: grid; place-items: center; padding: 16px; z-index: 50; }
        .gu .modal { background: white; border-radius: 14px; padding: 24px; width: min(100%, 520px); max-height: 90vh; overflow-y: auto; }
        .gu .modal h2 { margin: 0 0 6px; font-size: 19px; }
        .gu .modal label { display: block; font-weight: 600; font-size: 14px; margin: 12px 0 5px; }
        .gu .modal input, .gu .modal select, .gu .modal textarea { width: 100%; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; box-sizing: border-box; }
        .gu .modal input[disabled] { background: #f1f4f8; color: #65738a; }
        .gu .modal .pie { display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px; }
        .gu .error { color: #b42318; font-size: 13px; margin-top: 4px; }
        .gu .hint { color: #718096; font-size: 13.5px; line-height: 1.5; margin: 0; }
    </style>

    @if (session('ok')) <div class="ok">{{ session('ok') }}</div> @endif
    @if (session('error')) <div class="err">{{ session('error') }}</div> @endif

    <div class="barra">
        <input type="search" wire:model.live.debounce.400ms="buscar" placeholder="Buscar por nombre, correo o registro…" aria-label="Buscar usuarios">
        <select wire:model.live="rol" aria-label="Filtrar por rol">
            <option value="">Todos los roles</option>
            <option value="ESTUDIANTE">Solo estudiantes</option>
            <option value="TUTOR">Tutores</option>
            <option value="ADMINISTRADOR">Administradores</option>
        </select>
        <select wire:model.live="estado" aria-label="Filtrar por estado">
            <option value="">Activos e inactivos</option>
            <option value="ACTIVO">Activos</option>
            <option value="INACTIVO">Inactivos</option>
        </select>
        <button class="btn" wire:click="abrirCrear">+ Nuevo usuario</button>
    </div>

    <div class="tabla">
        <table>
            <thead>
                <tr><th>Usuario</th><th>Roles</th><th>Facultad / registro</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $u)
                    <tr wire:key="usuario-{{ $u->id }}">
                        <td>
                            <strong>{{ $u->fullName() }}</strong>
                            <div class="sub">{{ $u->email }}{{ $u->phone ? ' · '.$u->phone : '' }}</div>
                        </td>
                        <td>
                            @foreach ($u->roles as $r)
                                <span class="rol {{ $r }}">{{ ['ESTUDIANTE' => 'Estudiante', 'TUTOR' => 'Tutor', 'ADMINISTRADOR' => 'Admin'][$r] }}</span>
                            @endforeach
                        </td>
                        <td>
                            {{ $u->faculty ?? '—' }}
                            @if ($u->studentRegistry) <div class="sub">Reg. {{ $u->studentRegistry }}</div> @endif
                        </td>
                        <td>
                            <span class="estado {{ $u->active ? 'on' : 'off' }}">{{ $u->active ? 'Activo' : 'Inactivo' }}</span>
                        </td>
                        <td>
                            <div class="acciones">
                                <button class="btn sec mini" wire:click="abrirEditar('{{ $u->id }}')">Editar</button>
                                @if ($u->id !== $yo)
                                    @if ($u->active)
                                        <button class="btn peligro mini" wire:click="cambiarEstado('{{ $u->id }}', false)"
                                                wire:confirm="¿Desactivar la cuenta de {{ $u->fullName() }}? No podrá iniciar sesión.">Desactivar</button>
                                    @else
                                        <button class="btn sec mini" wire:click="cambiarEstado('{{ $u->id }}', true)">Activar</button>
                                    @endif
                                @endif
                                @if ($u->hasRole('TUTOR'))
                                    <button class="btn peligro mini" wire:click="abrirDegradar('{{ $u->id }}')">Quitar rol tutor</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align: center; color: #718096; padding: 28px;">No hay usuarios que coincidan con la búsqueda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pag">
        <span>{{ $total }} usuario(s) · página {{ $pagina }} de {{ $paginas }}</span>
        <button class="btn sec mini" wire:click="irAPagina({{ $pagina - 1 }})" @disabled($pagina <= 1)>Anterior</button>
        <button class="btn sec mini" wire:click="irAPagina({{ $pagina + 1 }})" @disabled($pagina >= $paginas)>Siguiente</button>
    </div>

    @if ($modal === 'crear')
        <div class="fondo" wire:click.self="cerrarModal">
            <form class="modal" wire:submit="crear">
                <h2>Nuevo usuario</h2>
                <p class="hint">El rol de tutor no se asigna aquí: se obtiene con una solicitud aprobada.</p>

                <label for="nuevoRol">Tipo de cuenta</label>
                <select id="nuevoRol" wire:model.live="nuevoRol">
                    <option value="ESTUDIANTE">Estudiante</option>
                    <option value="ADMINISTRADOR">Administrador</option>
                </select>
                @error('nuevoRol') <div class="error">{{ $message }}</div> @enderror

                <label for="nombres">Nombres</label>
                <input id="nombres" type="text" wire:model="nombres" maxlength="100">
                @error('nombres') <div class="error">{{ $message }}</div> @enderror

                <label for="apellidos">Apellidos</label>
                <input id="apellidos" type="text" wire:model="apellidos" maxlength="100">
                @error('apellidos') <div class="error">{{ $message }}</div> @enderror

                <label for="correo">Correo {{ $nuevoRol === 'ESTUDIANTE' ? '(Gmail)' : '' }}</label>
                <input id="correo" type="email" wire:model="correo" maxlength="150" autocomplete="off">
                @error('correo') <div class="error">{{ $message }}</div> @enderror

                <label for="contrasena">Contraseña inicial (mínimo 8 caracteres)</label>
                <input id="contrasena" type="password" wire:model="contrasena" autocomplete="new-password">
                @error('contrasena') <div class="error">{{ $message }}</div> @enderror

                @if ($nuevoRol === 'ADMINISTRADOR')
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" type="tel" wire:model="telefono" maxlength="20">
                    @error('telefono') <div class="error">{{ $message }}</div> @enderror

                    <label for="cargo">Cargo</label>
                    <input id="cargo" type="text" wire:model="cargo" maxlength="100">
                    @error('cargo') <div class="error">{{ $message }}</div> @enderror
                @endif

                <div class="pie">
                    <button type="button" class="btn sec" wire:click="cerrarModal">Cancelar</button>
                    <button type="submit" class="btn" wire:loading.attr="disabled">Crear cuenta</button>
                </div>
            </form>
        </div>
    @endif

    @if ($modal === 'editar')
        <div class="fondo" wire:click.self="cerrarModal">
            <form class="modal" wire:submit="guardarEdicion">
                <h2>Editar usuario</h2>

                <label for="e-correo">Correo</label>
                <input id="e-correo" type="email" value="{{ $correo }}" disabled>

                <label for="e-nombres">Nombres</label>
                <input id="e-nombres" type="text" wire:model="nombres" maxlength="100">
                @error('nombres') <div class="error">{{ $message }}</div> @enderror

                <label for="e-apellidos">Apellidos</label>
                <input id="e-apellidos" type="text" wire:model="apellidos" maxlength="100">
                @error('apellidos') <div class="error">{{ $message }}</div> @enderror

                <label for="e-telefono">Teléfono</label>
                <input id="e-telefono" type="tel" wire:model="telefono" maxlength="20">
                @error('telefono') <div class="error">{{ $message }}</div> @enderror

                <div class="pie">
                    <button type="button" class="btn sec" wire:click="cerrarModal">Cancelar</button>
                    <button type="submit" class="btn" wire:loading.attr="disabled">Guardar</button>
                </div>
            </form>
        </div>
    @endif

    @if ($modal === 'degradar' && $seleccionado)
        <div class="fondo" wire:click.self="cerrarModal">
            <form class="modal" wire:submit="degradar">
                <h2>Quitar rol de tutor</h2>
                <p class="hint">
                    <strong>{{ $seleccionado->fullName() }}</strong> dejará de ser tutor y conservará su cuenta de estudiante.
                    Sus tutorías <strong>publicadas</strong> se cancelarán y se avisará a los inscritos. Su historial se conserva.
                </p>

                <label for="motivo">Motivo</label>
                <textarea id="motivo" rows="3" wire:model="motivo" maxlength="500"></textarea>
                @error('motivo') <div class="error">{{ $message }}</div> @enderror

                <div class="pie">
                    <button type="button" class="btn sec" wire:click="cerrarModal">Cancelar</button>
                    <button type="submit" class="btn peligro" wire:loading.attr="disabled">Quitar rol de tutor</button>
                </div>
            </form>
        </div>
    @endif
</div>
