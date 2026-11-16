<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración | Tutorías UAGRM</title>
    @livewireStyles

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            color: #243047;
        }

        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 245px;
            height: 100vh;
            background: #142b4a;
            color: white;
            padding: 28px 18px;
            z-index: 10;
        }

        .admin-brand {
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 42px;
        }

        .admin-brand span { color: #65c3ed; }

        .admin-nav-title {
            color: #9eafc5;
            font-size: 12px;
            margin: 25px 12px 12px;
            text-transform: uppercase;
        }

        .admin-nav-item {
            display: block;
            padding: 13px 14px;
            margin: 6px 0;
            border-radius: 8px;
            color: #e5edf7;
            text-decoration: none;
        }

        .admin-nav-item:hover,
        .admin-nav-item.active {
            background: #24466f;
        }

        .admin-logout {
            position: absolute;
            bottom: 25px;
            left: 18px;
            right: 18px;
        }

        .admin-logout button {
            width: 100%;
            padding: 12px;
            background: #ffffff1a;
            color: white;
            border: 1px solid #ffffff40;
            border-radius: 8px;
            cursor: pointer;
        }

        .admin-main {
            margin-left: 245px;
            padding: 30px 38px;
            min-height: 100vh;
        }

        .admin-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .admin-topbar h1 {
            font-size: 25px;
            margin: 0;
        }

        .admin-user {
            color: #65738a;
            font-size: 14px;
        }

        @media(max-width: 850px) {
            .admin-sidebar { width: 190px; }
            .admin-main { margin-left: 190px; padding: 25px; }
        }

        @media(max-width: 600px) {
            .admin-sidebar {
                position: static;
                width: 100%;
                height: auto;
            }

            .admin-logout {
                position: static;
                margin-top: 20px;
            }

            .admin-main {
                margin-left: 0;
                padding: 20px;
            }

            .admin-topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    @php
        $user = auth()->user();
    @endphp

    <aside class="admin-sidebar">

        <div class="admin-brand">
            TUTORÍAS <span>UAGRM</span>
        </div>

        <x-menu-rol item-class="admin-nav-item" title-class="admin-nav-title" />

        <form class="admin-logout"
              method="POST"
              action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>

    </aside>

    <main class="admin-main">

        <header class="admin-topbar">
            <h1>{{ $title ?? 'Administración' }}</h1>

            <div class="admin-user" style="display: flex; align-items: center; gap: 14px;">
                <livewire:notificaciones.contador />
                <span>Sesión de: <strong>{{ $user->nombre_completo }}</strong></span>
            </div>
        </header>

        {{ $slot }}

    </main>

    @livewireScripts
</body>
</html>