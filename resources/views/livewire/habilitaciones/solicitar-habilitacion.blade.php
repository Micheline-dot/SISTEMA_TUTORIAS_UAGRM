
<div class="max-w-3xl mx-auto p-6 solicitud-tutor">
    <style>
        .solicitud-tutor { max-width: 760px; }
        .solicitud-tutor .bg-white { background: #fff; border: 1px solid #dce4ef; border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px #19365b10; }
        .solicitud-tutor h1 { margin: 0 0 6px; font-size: 1.5rem; color: #172b4d; }
        .solicitud-tutor p { color: #52657e; margin: 0 0 20px; }
        .solicitud-tutor form > div { margin-bottom: 18px; }
        .solicitud-tutor label { display: block; font-weight: 600; margin-bottom: 6px; color: #172b4d; }
        .solicitud-tutor input[type=file] { width: 100%; padding: 10px 12px; border: 1px solid #b8c6d8; border-radius: 8px; font: inherit; background: #fff; box-sizing: border-box; }
        .solicitud-tutor button { padding: 12px 22px; border: 0; border-radius: 8px; color: #fff; background: #174ea6; font: inherit; font-weight: 700; cursor: pointer; }
        .solicitud-tutor button:disabled { background: #8d9db3; }
        .solicitud-tutor .text-red-600 { color: #b42318; font-size: .9rem; }
        .solicitud-tutor .bg-green-100 { background: #e7f6ec; color: #1e6b3a; padding: 12px; border-radius: 8px; margin-bottom: 18px; }
        .solicitud-tutor .alerta { background: #fff4e5; color: #8a5300; padding: 16px; border-radius: 10px; margin-bottom: 18px; line-height: 1.5; }
        .solicitud-tutor .alerta a { display: inline-block; margin-top: 10px; padding: 10px 16px; background: #174ea6; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 700; }
        .solicitud-tutor .resumen { background: #f4f7fb; border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; display: grid; grid-template-columns: max-content 1fr; gap: 6px 16px; font-size: .95rem; }
        .solicitud-tutor .resumen dt { color: #52657e; }
        .solicitud-tutor .resumen dd { margin: 0; font-weight: 600; color: #172b4d; }
        .solicitud-tutor .estado { padding: 16px; border-radius: 10px; margin: 16px 0; line-height: 1.6; }
        .solicitud-tutor .estado.pendiente { background: #eef4ff; color: #1f4b8f; }
        .solicitud-tutor .estado.aprobada { background: #e7f6ec; color: #1e6b3a; }
        .solicitud-tutor .estado.rechazada { background: #fdecea; color: #9b2c22; }
        .solicitud-tutor .historial { margin-top: 22px; }
        .solicitud-tutor .historial h2 { margin: 0 0 12px; font-size: 1.15rem; color: #172b4d; }
        .solicitud-tutor table { width: 100%; border-collapse: collapse; font-size: .92rem; }
        .solicitud-tutor th, .solicitud-tutor td { text-align: left; padding: 9px 8px; border-bottom: 1px solid #e6ecf3; vertical-align: top; }
        .solicitud-tutor th { color: #52657e; font-weight: 600; }
        .solicitud-tutor .badge { padding: 3px 10px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
        .solicitud-tutor .badge.pendiente { background: #eef4ff; color: #1f4b8f; }
        .solicitud-tutor .badge.aprobada { background: #e7f6ec; color: #1e6b3a; }
        .solicitud-tutor .badge.rechazada { background: #fdecea; color: #9b2c22; }
        @media (max-width: 600px) { .solicitud-tutor .historial { overflow-x: auto; } }
    </style>

    @php
        $tipoPersonal = $perfil->academicDataCompleted() ? $perfil->registrationType : '';
    @endphp

    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-800">
            Solicitud de habilitación como tutor
        </h1>

        @if (session('mensaje'))
            <div class="bg-green-100 text-green-800 p-3 rounded-lg mb-5">
                {{ session('mensaje') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alerta">{{ session('error') }}</div>
        @endif

        @php $ultima = $solicitudes[0] ?? null; @endphp

        @if ($tienePendiente)

            <div class="estado pendiente">
                <strong>Tu solicitud está en revisión.</strong><br>
                La enviaste el {{ $ultima['fecha'] }}. El administrador revisará tus documentos;
                no puedes enviar otra solicitud mientras esta siga pendiente.
            </div>

        @elseif ($tieneAprobada)

            <div class="estado aprobada">
                <strong>¡Tu solicitud fue aprobada!</strong><br>
                Ya estás habilitado como tutor. Encontrarás tus opciones en el área de tutor.
            </div>

        @elseif (! $perfil->academicDataCompleted())

            <div class="alerta">
                <strong>Primero completa tus datos académicos.</strong><br>
                Para solicitar ser tutor debes registrar tu facultad y tu tipo de registro
                (estudiante, auxiliar o docente) con su registro o código en <strong>Mi perfil</strong>.
                Esa información se usa para validar tu solicitud.
                <br>
                <a href="{{ route('perfil') }}">Ir a Mi perfil</a>
            </div>

        @else

            @if ($ultima && $ultima['estado'] === 'RECHAZADA')
                <div class="estado rechazada">
                    <strong>Tu última solicitud fue rechazada</strong> ({{ $ultima['fecha'] }}).<br>
                    Motivo: {{ $ultima['comentario_rechazo'] ?: 'Sin observación.' }}<br>
                    Puedes corregir lo indicado y enviar una nueva solicitud.
                </div>
            @endif

            <p class="text-gray-500 mt-2 mb-6">
                Presenta los documentos requeridos según el tipo de registro de tu perfil.
            </p>

            <dl class="resumen">
                <dt>Tipo de personal</dt><dd>{{ ucfirst(mb_strtolower($tipoPersonal)) }}</dd>
                <dt>Facultad</dt><dd>{{ $perfil->faculty }}</dd>
                @if ($perfil->studentRegistry)
                    <dt>Registro universitario</dt><dd>{{ $perfil->studentRegistry }}</dd>
                @endif
                @if ($perfil->assistantRegistry)
                    <dt>Registro de auxiliar</dt><dd>{{ $perfil->assistantRegistry }}</dd>
                @endif
                @if ($perfil->teacherCode)
                    <dt>Código docente</dt><dd>{{ $perfil->teacherCode }}</dd>
                @endif
            </dl>

            <form wire:submit="enviar" enctype="multipart/form-data" class="space-y-5">

                @if ($tipoPersonal === 'AUXILIAR')
                    <div>
                        <label class="block font-semibold mb-2">Documento de respaldo (PDF)</label>
                        <input type="file" wire:model="documentoRespaldo" accept=".pdf">
                        @error('documentoRespaldo') <span class="text-red-600">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if ($tipoPersonal === 'ESTUDIANTE')
                    <div>
                        <label class="block font-semibold mb-2">Carta de solicitud (PDF)</label>
                        <input type="file" wire:model="cartaSolicitud" accept=".pdf">
                        @error('cartaSolicitud') <span class="text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Registro académico (PDF)</label>
                        <input type="file" wire:model="registroAcademico" accept=".pdf">
                        @error('registroAcademico') <span class="text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold mb-2">Historial académico (PDF)</label>
                        <input type="file" wire:model="historialAcademico" accept=".pdf">
                        @error('historialAcademico') <span class="text-red-600">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div>
                    <label class="block font-semibold mb-2">Currículum vitae (PDF)</label>
                    <input type="file" wire:model="curriculum" accept=".pdf">
                    @error('curriculum') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg">
                    <span wire:loading.remove>Enviar solicitud</span>
                    <span wire:loading>Enviando...</span>
                </button>
            </form>

        @endif
    </div>

    @if (count($solicitudes) > 0)
        <div class="bg-white historial">
            <h2>Mis solicitudes</h2>
            <table>
                <thead>
                    <tr><th>N.º</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th>Observación</th></tr>
                </thead>
                <tbody>
                    @foreach ($solicitudes as $s)
                        <tr wire:key="mi-solicitud-{{ $s['id_solicitud'] }}">
                            <td>#{{ $s['id_solicitud'] }}</td>
                            <td>{{ $s['fecha'] }}</td>
                            <td>{{ ucfirst(mb_strtolower($s['tipo_personal'])) }}</td>
                            <td><span class="badge {{ mb_strtolower($s['estado']) }}">{{ ['PENDIENTE' => 'Pendiente', 'APROBADA' => 'Aprobada', 'RECHAZADA' => 'Rechazada'][$s['estado']] ?? $s['estado'] }}</span></td>
                            <td>{{ $s['estado'] === 'RECHAZADA' ? ($s['comentario_rechazo'] ?: '—') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
