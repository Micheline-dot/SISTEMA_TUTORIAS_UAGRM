
<div class="login-container">

    <h1>Iniciar sesión</h1>

    <p>Accede con el correo electrónico de tu perfil.</p>

    @if (session('status'))
        <div class="notice">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login">

        <label for="email">Correo electrónico</label>
        <input
            id="email"
            type="email"
            wire:model="email"
            autocomplete="email"
            required
        >
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">Contraseña</label>
        <input
            id="password"
            type="password"
            wire:model="password"
            autocomplete="current-password"
            required
        >
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit">
            Iniciar sesión
        </button>

    </form>

    <p>
        ¿Eres nuevo?
        <a href="{{ route('register') }}">Registrar perfil</a>.
    </p>

</div>
