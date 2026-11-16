
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal | Tutorías UAGRM</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            color: #243047;
        }

        .sidebar {
            position: fixed;
            width: 245px;
            height: 100vh;
            background: #142b4a;
            color: white;
            padding: 28px 18px;
        }

        .brand {
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 42px;
        }

        .brand span { color: #65c3ed; }

        .nav-title {
            color: #9eafc5;
            font-size: 12px;
            margin: 25px 12px 12px;
            text-transform: uppercase;
        }

        .nav-item {
            display: block;
            padding: 13px 14px;
            margin: 6px 0;
            border-radius: 8px;
            color: #e5edf7;
            text-decoration: none;
        }

        .nav-item:hover,
        .nav-item.active {
            background: #24466f;
        }

        .main {
            margin-left: 245px;
            padding: 30px 38px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 35px;
        }

        .topbar h1 { font-size: 25px; margin: 0; }

        .user { color: #65738a; font-size: 14px; display: flex; align-items: center; gap: 14px; }

        .welcome {
            background: linear-gradient(120deg, #173c68, #246b9c);
            color: white;
            border-radius: 15px;
            padding: 32px;
            margin-bottom: 28px;
        }

        .welcome h2 { margin: 0 0 10px; font-size: 27px; }

        .welcome p {
            margin: 0;
            color: #e0edf8;
            line-height: 1.6;
        }

        .section-title { font-size: 19px; margin: 28px 0 16px; }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 23px;
            box-shadow: 0 3px 14px rgba(25, 45, 75, .06);
        }

        .card h3 { margin: 0 0 10px; font-size: 17px; }

        .card p {
            color: #718096;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }

        .card-link {
            display: block;
            color: inherit;
            text-decoration: none;
            transition: transform .2s;
        }

        .card-link:hover { transform: translateY(-3px); }

        .icon {
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #e7f2fb;
            color: #21618e;
            font-size: 22px;
            margin-bottom: 17px;
        }

        .notice {
            margin-top: 24px;
            background: white;
            padding: 23px;
            border-radius: 12px;
            color: #718096;
            line-height: 1.6;
        }

        .logout {
            position: absolute;
            bottom: 25px;
            left: 18px;
            right: 18px;
        }

        .logout button {
            width: 100%;
            padding: 12px;
            background: #ffffff1a;
            color: white;
            border: 1px solid #ffffff40;
            border-radius: 8px;
            cursor: pointer;
        }

        @media(max-width: 850px) {
            .sidebar { width: 190px; }
            .main { margin-left: 190px; padding: 25px; }
            .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media(max-width: 600px) {
            .sidebar { position: static; width: 100%; height: auto; }
            .logout { position: static; margin-top: 20px; }
            .main { margin-left: 0; padding: 20px; }
            .cards { grid-template-columns: 1fr; }
            .topbar { align-items: flex-start; flex-direction: column; }
        }
    </style>
    @livewireStyles
</head>

<body>

    @php
        $user = auth()->user();
        $roles = $user->nombresRoles();

        $esEstudiante = in_array('ESTUDIANTE', $roles);
        $esTutor = in_array('TUTOR', $roles);
        $esAdministrador = in_array('ADMINISTRADOR', $roles);
    @endphp

    <aside class="sidebar">

        <div class="brand">TUTORÍAS <span>UAGRM</span></div>

        <x-menu-rol />

        <form class="logout" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>

    </aside>

    <main class="main">

        <header class="topbar">
            <h1>Panel principal</h1>

            <div class="user">
                <livewire:notificaciones.contador />
                <span>Sesión de: <strong>{{ $user->nombre_completo }}</strong></span>
            </div>
        </header>

        <section class="welcome">

            @if ($esAdministrador)
                <h2>¡Bienvenido/a, {{ $user->nombre_completo }}!</h2>
                <p>Desde aquí podrás administrar las solicitudes de habilitación de tutores y supervisar el funcionamiento del sistema.</p>

            @elseif ($esTutor)
                <h2>¡Bienvenido/a, {{ $user->nombre_completo }}!</h2>
                <p>Este es tu espacio de tutor. Aquí podrás gestionar tus actividades académicas y tutorías.</p>

            @else
                <h2>¡Bienvenido/a, {{ $user->nombre_completo }}!</h2>
                <p>Este es tu espacio académico. Desde aquí podrás encontrar tutorías, consultar tus inscripciones y acceder a tus actividades.</p>
            @endif

        </section>

        <h2 class="section-title">Accesos principales</h2>

        <section class="cards">

            @if ($esAdministrador)

                <a class="card card-link" href="{{ route('admin.solicitudes-tutor') }}">
                    <div class="icon">▤</div>
                    <h3>Solicitudes de tutores</h3>
                    <p>Revisa, aprueba o rechaza las solicitudes de habilitación de tutores.</p>
                </a>

                <a class="card card-link" href="{{ route('admin.usuarios') }}">
                    <div class="icon">☷</div>
                    <h3>Gestionar usuarios</h3>
                    <p>Crea cuentas, edita datos, activa o desactiva usuarios y retira el rol de tutor.</p>
                </a>

            @else

                <a class="card card-link" href="{{ route('estudiante.inscripciones') }}">
    <div class="icon">▣</div>
    <h3>Mis inscripciones</h3>
    <p>Consulta y administra las tutorías en las que te has inscrito.</p>
</a>

            @endif

            <a class="card card-link" href="{{ route('perfil') }}">
                <div class="icon">♙</div>
                <h3>Mi perfil</h3>
                <p>Actualiza tu foto, teléfono y nombre, y registra tus datos académicos.</p>
            </a>

            @if ($esTutor)

                <a class="card card-link" href="{{ route('tutor.perfil-academico') }}">
                    <div class="icon">✎</div>
                    <h3>Perfil académico</h3>
                    <p>Completa tu biografía, conocimientos y enlaces para tus estudiantes.</p>
                </a>

            @endif

            @if ($esEstudiante && !$esTutor && !$esAdministrador)

                <a class="card card-link" href="{{ route('habilitacion.solicitar') }}">
                    <div class="icon">✚</div>
                    <h3>Quiero ser tutor</h3>
                    <p>Presenta tu documentación y solicita la habilitación como tutor.</p>
                </a>

            @endif

        </section>

        <h2 class="section-title">Actividad académica</h2>

        <section class="notice">
            <strong>Bienvenido al sistema de tutorías.</strong><br>
            Aquí aparecerá la información de tus tutorías, solicitudes e inscripciones cuando estén disponibles los módulos correspondientes.
        </section>

    </main>

    @livewireScripts
</body>
</html>
