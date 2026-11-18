<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Tutorías UAGRM</title>
    @livewireStyles
    @php
        $fondo = app(\App\Business\Configuracion\UseCases\ManageLoginBackground::class)->currentPath();
    @endphp
    <style>
        :root { font-family: Inter, ui-sans-serif, system-ui, sans-serif; color: #172b4d; background: #f4f7fb; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        main { width: min(100%, 520px); background: white; border: 1px solid #dce4ef; border-radius: 16px; padding: 32px; box-shadow: 0 16px 48px #19365b12; }
        h1 { margin: 0 0 8px; font-size: 1.65rem; }
        p { color: #52657e; line-height: 1.5; }
        label { display: block; font-weight: 600; margin: 16px 0 6px; }
        input, select { width: 100%; padding: 11px 12px; border: 1px solid #b8c6d8; border-radius: 8px; font: inherit; background: white; }
        button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 8px; color: white; background: #174ea6; font: inherit; font-weight: 700; cursor: pointer; }
        button:disabled { background: #8d9db3; cursor: not-allowed; }
        a { color: #174ea6; }
        .error { color: #b42318; font-size: .9rem; margin-top: 5px; }
        .notice { border-radius: 8px; padding: 12px; background: #eef5ff; color: #244d80; margin: 16px 0; }
        .auth-logo { display: flex; justify-content: center; margin: -4px 0 22px; padding-bottom: 20px; border-bottom: 1px solid #e6ecf4; }
        /* (+) Foto de fondo configurable: capa oscura para que el formulario siga legible */
        body.con-fondo { background-color: #0f2340; background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; }
        body.con-fondo main { border-color: transparent; box-shadow: 0 24px 70px rgba(5, 15, 35, .45); }
        @media (max-width: 600px) { body.con-fondo { background-attachment: scroll; } }
        .empty { border: 1px solid #f0d4a8; background: #fff9ed; border-radius: 8px; padding: 12px; color: #754c12; margin-top: 16px; }
    </style>
</head>
@if ($fondo)
<body class="con-fondo" style="background-image: linear-gradient(rgba(10, 25, 50, .45), rgba(10, 25, 50, .45)), url('{{ route('sistema.fondo-login', ['v' => substr(md5($fondo), 0, 8)]) }}');">
@else
<body>
@endif
    <main>
        <div class="auth-logo"><x-logo tema="claro" tamano="lg" /></div>
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
