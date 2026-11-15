
<div class="register-container">

    <h1>Registrar perfil</h1>

    <p>Crea tu cuenta para utilizar el Sistema de Apoyo Académico UAGRM.</p>

    @if (count($this->faculties) === 0)
        <div class="empty">
            El catálogo oficial de facultades aún no está cargado.
            El registro estará disponible cuando se cargue.
        </div>
    @endif

    <form wire:submit="register">

        <label for="fullName">Nombre completo</label>
        <input id="fullName" type="text" wire:model="fullName" autocomplete="name" required>
        @error('fullName') <div class="error">{{ $message }}</div> @enderror

        <label for="email">Correo electrónico</label>
        <input id="email" type="email" wire:model="email" autocomplete="email" required>
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label for="password">Contraseña</label>
        <input id="password" type="password" wire:model="password" autocomplete="new-password" required>
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <label for="facultyId">Facultad</label>
        <select id="facultyId" wire:model="facultyId" required>
            <option value="">Selecciona una facultad</option>
            @foreach ($this->faculties as $faculty)
                <option value="{{ $faculty['id'] }}">{{ $faculty['name'] }}</option>
            @endforeach
        </select>
        @error('facultyId') <div class="error">{{ $message }}</div> @enderror

        <label for="phone">Teléfono</label>
        <input id="phone" type="tel" wire:model="phone" autocomplete="tel" required>
        @error('phone') <div class="error">{{ $message }}</div> @enderror

        <button type="submit" @disabled(count($this->faculties) === 0)>
            Crear perfil
        </button>

    </form>

    <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión</a>.</p>

</div>
