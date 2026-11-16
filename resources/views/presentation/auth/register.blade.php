
<div class="register-container">

    <h1>Registrar perfil</h1>

    <p>Crea tu cuenta para utilizar el Sistema de Apoyo Académico UAGRM.
       Tu teléfono, facultad y registro universitario los completarás después en <strong>Mi perfil</strong>.</p>

    <form wire:submit="register">

        <label for="firstNames">Nombres</label>
        <input id="firstNames" type="text" wire:model="firstNames" autocomplete="given-name" maxlength="100" required>
        @error('firstNames') <div class="error">{{ $message }}</div> @enderror

        <label for="lastNames">Apellidos</label>
        <input id="lastNames" type="text" wire:model="lastNames" autocomplete="family-name" maxlength="100" required>
        @error('lastNames') <div class="error">{{ $message }}</div> @enderror

        <label for="email">Correo electrónico (Gmail)</label>
        <input id="email" type="email" wire:model="email" autocomplete="email" placeholder="usuario@gmail.com" maxlength="150" required>
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label for="password">Contraseña</label>
        <input id="password" type="password" wire:model="password" autocomplete="new-password" minlength="8" required>
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" minlength="8" required>

        <button type="submit">Crear perfil</button>

    </form>

    <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a>.</p>

</div>
