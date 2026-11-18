<div class="cl">
    <style>
        .cl { display: grid; gap: 16px; max-width: 900px; }
        .cl .panel { background: white; border-radius: 12px; padding: 22px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .cl h2 { margin: 0 0 6px; font-size: 18px; color: #172b4d; }
        .cl .hint { color: #65738a; font-size: 14px; margin: 0 0 14px; line-height: 1.55; }
        .cl .vistas { display: grid; grid-template-columns: 1fr 260px; gap: 16px; }
        .cl .vista { border-radius: 12px; padding: 18px; display: grid; gap: 8px; align-content: start; }
        .cl .vista small { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }
        .cl .vista.claro { background: #f4f7fb; border: 1px solid #dce4ef; }
        .cl .vista.claro small { color: #65738a; }
        .cl .vista.oscuro { background: #142b4a; }
        .cl .vista.oscuro small { color: #9eafc5; }
        .cl .marco { min-height: 110px; display: grid; place-items: center; }
        .cl .marco img { max-width: 100%; max-height: 110px; object-fit: contain; }
        .cl .vista.oscuro .marco img { max-height: 64px; }
        .cl label { display: block; font-weight: 600; font-size: 14px; margin: 16px 0 6px; }
        .cl input[type=file] { width: 100%; padding: 10px; border: 1px dashed #b8c6d8; border-radius: 8px; background: #fafcff; font: inherit; box-sizing: border-box; }
        .cl .acciones { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; }
        .cl .btn { padding: 10px 16px; border: 0; border-radius: 8px; background: #174ea6; color: white; font: inherit; font-weight: 700; cursor: pointer; }
        .cl .btn:disabled { background: #8d9db3; cursor: not-allowed; }
        .cl .btn.peligro { background: #fdecea; color: #9b2c22; }
        .cl .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .cl .error { color: #b42318; font-size: 13px; margin-top: 5px; }
        .cl .sub { color: #718096; font-size: 13px; }
        .cl .fondo-previa { position: relative; height: 240px; border-radius: 12px; background: #f4f7fb; border: 1px solid #dce4ef; background-size: cover; background-position: center; display: grid; place-items: center; overflow: hidden; }
        .cl .fondo-previa small { position: absolute; left: 12px; top: 10px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #fff; background: rgba(15, 30, 55, .55); padding: 3px 8px; border-radius: 6px; }
        .cl .mini-login { width: 190px; background: white; border-radius: 10px; padding: 14px; display: grid; gap: 7px; box-shadow: 0 12px 30px rgba(5, 15, 35, .35); }
        .cl .mini-login .logo-sis img { max-height: 40px; }
        .cl .mini-login .logo-sis .texto { font-size: 14px; }
        .cl .mini-login .linea { height: 8px; background: #e6ecf4; border-radius: 4px; }
        .cl .mini-login .linea.corta { width: 65%; }
        .cl .mini-login .boton { height: 14px; background: #174ea6; border-radius: 4px; margin-top: 3px; }
        @media (max-width: 720px) { .cl .vistas { grid-template-columns: 1fr; } }
    </style>

    @if (session('ok')) <div class="ok">{{ session('ok') }}</div> @endif

    <section class="panel">
        <h2>Logo del sistema</h2>
        <p class="hint">Se muestra centrado en las pantallas de inicio de sesión y registro, y en la parte superior del menú lateral de todos los usuarios.
            Usa una imagen PNG con fondo transparente para que se vea bien sobre el fondo claro y el oscuro.</p>

        @php
            $previa = $logo && ! $errors->has('logo') && method_exists($logo, 'isPreviewable') && $logo->isPreviewable() ? $logo->temporaryUrl() : null;
        @endphp

        <div class="vistas">
            <div class="vista claro">
                <small>{{ $previa ? 'Vista previa' : 'Actual' }} · Inicio de sesión</small>
                <div class="marco">
                    @if ($previa)
                        <img src="{{ $previa }}" alt="Vista previa del logo">
                    @else
                        <x-logo tema="claro" tamano="lg" />
                    @endif
                </div>
            </div>
            <div class="vista oscuro">
                <small>{{ $previa ? 'Vista previa' : 'Actual' }} · Menú</small>
                <div class="marco">
                    @if ($previa)
                        <img src="{{ $previa }}" alt="">
                    @else
                        <x-logo tema="oscuro" />
                    @endif
                </div>
            </div>
        </div>

        <form wire:submit="guardar">
            <label for="logo">{{ $tieneLogo ? 'Reemplazar logo' : 'Subir logo' }} <span class="sub">(PNG, JPG o WEBP · máx. 2 MB)</span></label>
            <input id="logo" type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp">
            <div wire:loading wire:target="logo" class="sub" style="margin-top: 5px;">Subiendo imagen…</div>
            @error('logo') <div class="error">{{ $message }}</div> @enderror

            <div class="acciones">
                <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="guardar,logo">Guardar logo</button>
                @if ($tieneLogo)
                    <button type="button" class="btn peligro" wire:click="quitar" wire:confirm="¿Quitar el logo? Se mostrará el nombre del sistema en texto.">Quitar logo</button>
                @endif
            </div>
        </form>
    </section>

    {{-- (+) Fondo del inicio de sesión --}}
    <section class="panel">
        <h2>Fondo del inicio de sesión</h2>
        <p class="hint">Foto que aparece detrás del formulario de inicio de sesión y de registro. Se le aplica una capa oscura suave para que el formulario se lea bien.
            Recomendado: imagen horizontal de al menos 1600 × 900 px (por ejemplo, el campus de la UAGRM), en JPG o WEBP para que pese poco.</p>

        @php
            $previaFondo = $fondo && ! $errors->has('fondo') && method_exists($fondo, 'isPreviewable') && $fondo->isPreviewable() ? $fondo->temporaryUrl() : null;
            $verFondo = $previaFondo ?? $urlFondo;
        @endphp

        <div class="fondo-previa" @if ($verFondo) style="background-image: linear-gradient(rgba(10, 25, 50, .45), rgba(10, 25, 50, .45)), url('{{ $verFondo }}');" @endif>
            <small>{{ $previaFondo ? 'Vista previa' : ($urlFondo ? 'Actual' : 'Sin foto: fondo claro por defecto') }}</small>
            <div class="mini-login">
                <x-logo tema="claro" tamano="sm" />
                <span class="linea"></span><span class="linea corta"></span><span class="boton"></span>
            </div>
        </div>

        <form wire:submit="guardarFondo">
            <label for="fondo">{{ $urlFondo ? 'Reemplazar fondo' : 'Subir fondo' }} <span class="sub">(PNG, JPG o WEBP · máx. 5 MB; el servidor puede limitarlo a menos)</span></label>
            <input id="fondo" type="file" wire:model="fondo" accept="image/png,image/jpeg,image/webp">
            <div wire:loading wire:target="fondo" class="sub" style="margin-top: 5px;">Subiendo imagen…</div>
            @error('fondo') <div class="error">{{ $message }}</div> @enderror

            <div class="acciones">
                <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="guardarFondo,fondo">Guardar fondo</button>
                @if ($urlFondo)
                    <button type="button" class="btn peligro" wire:click="quitarFondo" wire:confirm="¿Quitar la foto de fondo? El inicio de sesión volverá al fondo claro.">Quitar fondo</button>
                @endif
            </div>
        </form>
    </section>
</div>
