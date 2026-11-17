<div class="pa">
    <style>
        .pa { max-width: 860px; display: grid; gap: 18px; }
        .pa .panel { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .pa h2 { margin: 0 0 6px; font-size: 18px; }
        .pa .hint { color: #718096; font-size: 14px; margin: 0 0 12px; line-height: 1.5; }
        .pa label { display: block; font-weight: 600; font-size: 14px; margin: 16px 0 6px; }
        .pa textarea, .pa input[type=text] { width: 100%; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; box-sizing: border-box; }
        .pa textarea { resize: vertical; }
        .pa .fila { display: flex; gap: 8px; }
        .pa .fila input { flex: 1; }
        .pa .btn { padding: 10px 18px; border: 0; border-radius: 8px; background: #174ea6; color: white; font-weight: 700; cursor: pointer; font: inherit; }
        .pa .btn.sec { background: #e9eef5; color: #243047; }
        .pa .tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .pa .tag { background: #e7f2fb; color: #1d5a86; border-radius: 999px; padding: 6px 8px 6px 12px; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; }
        .pa .tag button { border: 0; background: #c9e2f5; color: #1d5a86; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; line-height: 1; }
        .pa .error { color: #b42318; font-size: 13px; margin-top: 5px; }
        .pa .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .pa .warn { background: #fff4e5; color: #8a5300; border-radius: 8px; padding: 12px 14px; font-size: 14px; }
        .pa .contador { color: #8492a6; font-size: 12px; text-align: right; }
        .pa .opc { color: #8492a6; font-weight: 400; }
        .pa .enlaces { display: grid; gap: 8px; }
        .pa .enlace .fila { align-items: center; }
        .pa .enlace-icono { font-size: 16px; }
        .pa .btn.quitar { background: #fdecea; color: #9b2c22; padding: 9px 12px; }
        .pa .btn.agregar { margin-top: 10px; }
        .pa .vacio-enlaces { color: #8492a6; font-size: 14px; border: 1px dashed #c5d1e0; border-radius: 8px; padding: 12px; text-align: center; }
    </style>

    @if (session('perfil_ok'))
        <div class="ok">{{ session('perfil_ok') }}</div>
    @endif

    @unless ($completo)
        <div class="warn">
            Tu perfil académico está incompleto. Agrega una <strong>biografía</strong> y al menos un
            <strong>conocimiento</strong>: los estudiantes lo verán en el detalle de tus tutorías.
        </div>
    @endunless

    <form class="panel" wire:submit="guardar">
        <h2>Perfil académico</h2>
        <p class="hint">Esta información se muestra a los estudiantes junto a tus tutorías.</p>

        <label for="biografia">Biografía</label>
        <textarea id="biografia" rows="6" wire:model.live.debounce.500ms="biografia" maxlength="2000"
                  placeholder="Cuéntales a los estudiantes tu experiencia, formación y cómo enseñas."></textarea>
        <div class="contador">{{ mb_strlen($biografia) }}/2000</div>
        @error('biografia') <div class="error">{{ $message }}</div> @enderror

        <label for="nuevoConocimiento">Conocimientos</label>
        <div class="fila">
            <input id="nuevoConocimiento" type="text" wire:model="nuevoConocimiento" maxlength="60"
                   placeholder="Ej. Cálculo I, Programación en Python…" wire:keydown.enter.prevent="agregarConocimiento">
            <button type="button" class="btn sec" wire:click="agregarConocimiento">Agregar</button>
        </div>
        @error('nuevoConocimiento') <div class="error">{{ $message }}</div> @enderror
        <div class="tags">
            @forelse ($conocimientos as $i => $c)
                <span class="tag" wire:key="con-{{ $i }}-{{ md5($c) }}">
                    {{ $c }}
                    <button type="button" wire:click="quitarConocimiento({{ $i }})" aria-label="Quitar {{ $c }}">×</button>
                </span>
            @empty
                <span class="hint">Aún no agregaste conocimientos.</span>
            @endforelse
        </div>

        <label>Enlaces <span class="opc">(opcional · máximo {{ \App\Business\Usuarios\UseCases\ManageAcademicProfile::MAX_LINKS }})</span></label>
        <p class="hint" style="margin-bottom: 8px;">LinkedIn, GitHub, tu página personal o un portafolio.</p>

        <div class="enlaces">
            @forelse ($enlaces as $i => $enlace)
                <div class="enlace" wire:key="enlace-{{ $i }}">
                    <div class="fila">
                        <span class="enlace-icono" aria-hidden="true">🔗</span>
                        <input type="url" wire:model.blur="enlaces.{{ $i }}" maxlength="255"
                               placeholder="https://www.linkedin.com/in/tu-perfil" aria-label="Enlace {{ $i + 1 }}">
                        <button type="button" class="btn quitar" wire:click="quitarEnlace({{ $i }})" aria-label="Quitar enlace {{ $i + 1 }}">Quitar</button>
                    </div>
                    @error('enlaces.'.$i) <div class="error">{{ $message }}</div> @enderror
                </div>
            @empty
                <div class="vacio-enlaces">Aún no agregaste enlaces.</div>
            @endforelse
        </div>

        @if (count($enlaces) < \App\Business\Usuarios\UseCases\ManageAcademicProfile::MAX_LINKS)
            <button type="button" class="btn sec agregar" wire:click="agregarEnlace">+ Agregar enlace</button>
        @else
            <p class="hint" style="margin-top: 8px;">Llegaste al máximo de enlaces.</p>
        @endif
        @error('enlaces') <div class="error">{{ $message }}</div> @enderror

        <div style="margin-top: 18px;">
            <button class="btn" type="submit" wire:loading.attr="disabled">Guardar perfil académico</button>
        </div>
    </form>
</div>
