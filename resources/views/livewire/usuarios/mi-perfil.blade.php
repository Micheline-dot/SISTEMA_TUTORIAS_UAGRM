<div class="perfil">
    <style>
        .perfil { display: grid; gap: 22px; max-width: 900px; }
        .perfil .panel { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .perfil h2 { margin: 0 0 6px; font-size: 18px; }
        .perfil .hint { color: #718096; font-size: 14px; margin: 0 0 16px; line-height: 1.5; }
        .perfil label { display: block; font-weight: 600; font-size: 14px; margin: 14px 0 6px; }
        .perfil input[type=text], .perfil input[type=tel], .perfil input[type=email], .perfil select {
            width: 100%; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; background: white;
        }
        .perfil input[disabled] { background: #f1f4f8; color: #65738a; }
        .perfil .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 18px; }
        .perfil .btn { margin-top: 18px; padding: 10px 18px; border: 0; border-radius: 8px; background: #174ea6; color: white; font-weight: 700; cursor: pointer; }
        .perfil .btn.sec { background: #e9eef5; color: #243047; }
        .perfil .btn:disabled { opacity: .6; cursor: wait; }
        .perfil .error { color: #b42318; font-size: 13px; margin-top: 5px; }
        .perfil .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .perfil .warn { background: #fff4e5; color: #8a5300; border-radius: 8px; padding: 12px 14px; font-size: 14px; }
        .perfil .foto-row { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .perfil .avatar { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; background: #e7f2fb; display: grid; place-items: center; font-size: 34px; color: #21618e; font-weight: bold; }
        .perfil .tipos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .perfil .tipo { position: relative; border: 2px solid #dbe3ee; border-radius: 12px; padding: 16px 14px; cursor: pointer; display: grid; gap: 4px; margin: 0; font-weight: normal; transition: border-color .15s, background .15s; }
        .perfil .tipo:hover { border-color: #9bb5dc; }
        .perfil .tipo.sel { border-color: #174ea6; background: #eef4ff; }
        .perfil .tipo.sel::after { content: "✓"; position: absolute; top: 10px; right: 12px; color: #174ea6; font-weight: 700; }
        .perfil .tipo-radio { position: absolute; opacity: 0; pointer-events: none; }
        .perfil .tipo:focus-within { outline: 3px solid #9bb5dc; outline-offset: 2px; }
        .perfil .tipo-icono { font-size: 24px; }
        .perfil .tipo-titulo { font-weight: 700; font-size: 15px; color: #172b4d; }
        .perfil .tipo-detalle { font-size: 13px; color: #65738a; line-height: 1.4; }
        @media (max-width: 700px) { .perfil .tipos { grid-template-columns: 1fr; } }
        .perfil dl { display: grid; grid-template-columns: max-content 1fr; gap: 10px 18px; margin: 0; font-size: 15px; }
        .perfil dt { color: #65738a; }
        .perfil dd { margin: 0; font-weight: 600; }
        @media (max-width: 600px) { .perfil .grid2 { grid-template-columns: 1fr; } }
    </style>

    @if (session('perfil_ok'))
        <div class="ok">{{ session('perfil_ok') }}</div>
    @endif
    @if (session('perfil_error'))
        <div class="warn">{{ session('perfil_error') }}</div>
    @endif

    @if (! $profile->isAdministrator() && ! $profile->academicDataCompleted())
        <div class="warn">
            <strong>Completa tus datos académicos:</strong> selecciona tu facultad e ingresa tu registro.
            Son obligatorios para solicitar ser tutor.
            <a href="#datos-academicos">Ir a datos académicos ↓</a>
        </div>
    @endif

    {{-- Foto de perfil --}}
    <section class="panel">
        <h2>Foto de perfil</h2>
        <p class="hint">JPG, PNG o WEBP de hasta 2 MB. Puedes cambiarla cuando quieras.</p>

        <div class="foto-row">
            @if ($photo && ! $errors->has('photo'))
                <img class="avatar" src="{{ $photo->temporaryUrl() }}" alt="Vista previa">
            @elseif ($profile->hasPhoto())
                <img class="avatar" src="{{ route('perfil.foto', ['userId' => $profile->userId]) }}?v={{ md5($profile->photoPath) }}" alt="Foto de perfil">
            @else
                <div class="avatar">{{ mb_strtoupper(mb_substr($profile->firstNames, 0, 1)) }}</div>
            @endif

            <div>
                <input type="file" wire:model="photo" accept=".jpg,.jpeg,.png,.webp">
                <div wire:loading wire:target="photo" class="hint">Subiendo imagen…</div>
                @error('photo') <div class="error">{{ $message }}</div> @enderror
                <div>
                    @if ($photo)
                        <button class="btn" wire:click="guardarFoto" wire:loading.attr="disabled">Guardar foto</button>
                    @endif
                    @if ($profile->hasPhoto())
                        <button class="btn sec" wire:click="quitarFoto" wire:confirm="¿Quitar tu foto de perfil?">Quitar foto</button>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Datos personales --}}
    <section class="panel">
        <h2>Datos personales</h2>
        <p class="hint">El correo es tu identificador de acceso y no puede modificarse.</p>

        <form wire:submit="guardarDatosPersonales">
            <div class="grid2">
                <div>
                    <label for="firstNames">Nombres</label>
                    <input id="firstNames" type="text" wire:model="firstNames" maxlength="100">
                    @error('firstNames') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label for="lastNames">Apellidos</label>
                    <input id="lastNames" type="text" wire:model="lastNames" maxlength="100">
                    @error('lastNames') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label for="email">Correo electrónico</label>
                    <input id="email" type="email" value="{{ $profile->email }}" disabled>
                </div>
                <div>
                    <label for="phone">Teléfono</label>
                    <input id="phone" type="tel" wire:model="phone" placeholder="Ej. 70012345" maxlength="20">
                    @error('phone') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
            <button class="btn" type="submit" wire:loading.attr="disabled">Guardar cambios</button>
        </form>
    </section>

    {{-- Datos académicos (una sola vez) --}}
    @unless ($profile->isAdministrator())
        <section class="panel" id="datos-academicos">
            <h2>Datos académicos</h2>

            @if ($profile->academicDataCompleted())
                <p class="hint">Estos datos se registraron una sola vez y no pueden modificarse.
                   Se usan como referencia al validar tu solicitud de tutor.</p>
                <dl>
                    <dt>Facultad</dt><dd>{{ $profile->faculty }}</dd>
                    <dt>Tipo de registro</dt><dd>{{ ucfirst(mb_strtolower($profile->registrationType)) }}</dd>
                    @if ($profile->studentRegistry)
                        <dt>Registro universitario</dt><dd>{{ $profile->studentRegistry }}</dd>
                    @endif
                    @if ($profile->assistantRegistry)
                        <dt>Código de auxiliar</dt><dd>{{ $profile->assistantRegistry }}</dd>
                    @endif
                    @if ($profile->teacherCode)
                        <dt>Código de docente</dt><dd>{{ $profile->teacherCode }}</dd>
                    @endif
                </dl>
            @else
                <div class="warn">Solo podrás registrar estos datos <strong>una vez</strong>. Verifica que sean correctos antes de guardar.</div>

                <form wire:submit="guardarDatosAcademicos">
                    <label for="faculty">Facultad</label>
                    <select id="faculty" wire:model="faculty">
                        <option value="">Selecciona una facultad</option>
                        @foreach ($faculties as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                    @error('faculty') <div class="error">{{ $message }}</div> @enderror

                    <label>¿Cuál es tu tipo de registro?</label>
                    <div class="tipos" role="radiogroup" aria-label="Tipo de registro">
                        @foreach ([
                            'ESTUDIANTE' => ['Estudiante', 'Ingresas tu registro universitario.', '🎓'],
                            'AUXILIAR' => ['Auxiliar', 'Ingresas tu registro universitario y tu código de auxiliar.', '🧑‍🏫'],
                            'DOCENTE' => ['Docente', 'Ingresas tu código de docente.', '📘'],
                        ] as $value => [$label, $detalle, $icono])
                            <label class="tipo {{ $registrationType === $value ? 'sel' : '' }}">
                                <input type="radio" class="tipo-radio" wire:model.live="registrationType" value="{{ $value }}">
                                <span class="tipo-icono" aria-hidden="true">{{ $icono }}</span>
                                <span class="tipo-titulo">{{ $label }}</span>
                                <span class="tipo-detalle">{{ $detalle }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('registrationType') <div class="error">{{ $message }}</div> @enderror

                    <div class="grid2" style="margin-top: 6px;">
                        @if (in_array($registrationType, ['ESTUDIANTE', 'AUXILIAR'], true))
                            <div>
                                <label for="studentRegistry">Registro universitario</label>
                                <input id="studentRegistry" type="text" wire:model="studentRegistry" inputmode="numeric" placeholder="Ej. 220166218" maxlength="15">
                                @error('studentRegistry') <div class="error">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        @if ($registrationType === 'AUXILIAR')
                            <div>
                                <label for="assistantRegistry">Código de auxiliar</label>
                                <input id="assistantRegistry" type="text" wire:model="assistantRegistry" maxlength="30">
                                @error('assistantRegistry') <div class="error">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        @if ($registrationType === 'DOCENTE')
                            <div>
                                <label for="teacherCode">Código de docente</label>
                                <input id="teacherCode" type="text" wire:model="teacherCode" maxlength="30">
                                @error('teacherCode') <div class="error">{{ $message }}</div> @enderror
                            </div>
                        @endif
                    </div>

                    <button class="btn" type="submit" wire:loading.attr="disabled"
                            wire:confirm="Estos datos no podrán modificarse después. ¿Confirmas que son correctos?">
                        Registrar datos académicos
                    </button>
                </form>
            @endif
        </section>
    @endunless
</div>
