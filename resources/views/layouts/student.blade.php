<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar tutorías | Tutorías UAGRM</title>
    @livewireStyles
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
        .brand { font-size: 21px; font-weight: bold; margin-bottom: 42px; }
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
        .nav-item.active, .nav-item:hover { background: #24466f; }
        .logout { position: absolute; bottom: 25px; left: 18px; right: 18px; }
        .logout button {
            width: 100%;
            padding: 12px;
            background: #ffffff1a;
            color: white;
            border: 1px solid #ffffff40;
            border-radius: 8px;
            cursor: pointer;
        }
        .main { margin-left: 245px; padding: 30px 38px; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .topbar h1 { font-size: 25px; margin: 0; }
        .user { color: #65738a; font-size: 14px; }
        @media(max-width: 850px) {
            .sidebar { width: 190px; }
            .main { margin-left: 190px; padding: 25px; }
        }
        @media(max-width: 600px) {
            .sidebar { position: static; width: 100%; height: auto; }
            .logout { position: static; margin-top: 20px; }
            .main { margin-left: 0; padding: 20px; }
            .topbar { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand">TUTORÍAS <span>UAGRM</span></div>
        <div class="nav-title">Menú principal</div>
        <a class="nav-item" href="{{ route('home') }}">⌂ &nbsp; Inicio</a>
        <a class="nav-item active" href="{{ route('estudiante.catalogo') }}">Buscar tutorías</a>
        <a class="nav-item" href="{{ route('estudiante.inscripciones') }}">Mis inscripciones</a>
        <div class="nav-title">Mi cuenta</div>
        <a class="nav-item" href="{{ route('habilitacion.solicitar') }}">✦ &nbsp; Quiero ser tutor</a>
        <form class="logout" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>
    </aside>

    <main class="main">
        <header class="topbar">
            <h1>Buscar tutorías</h1>
            <div class="user">
                Sesión de: <strong>{{ auth()->user()->nombre_completo }}</strong>
            </div>
        </header>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>