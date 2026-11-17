<div class="st">
    <style>
        .st { max-width: 920px; display: grid; gap: 18px; }
        .st .panel { background: #fff; border-radius: 14px; padding: 26px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .st h1 { margin: 0 0 6px; font-size: 1.4rem; color: #172b4d; }
        .st h2 { margin: 0 0 6px; font-size: 1.12rem; color: #172b4d; }
        .st p { color: #52657e; margin: 0 0 14px; line-height: 1.55; }
        .st .btn { padding: 11px 20px; border: 0; border-radius: 8px; color: #fff; background: #174ea6; font: inherit; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
        .st .btn:disabled { background: #8d9db3; cursor: not-allowed; }
        .st .btn.sec { background: #e9eef5; color: #243047; }
        .st .btn.ok-btn { background: #16804a; }
        .st .msg-ok { background: #e7f6ec; color: #1e6b3a; padding: 12px 14px; border-radius: 8px; }
        .st .alerta { background: #fff4e5; color: #8a5300; padding: 16px; border-radius: 10px; line-height: 1.55; }
        .st .alerta a.btn { margin-top: 10px; }
        .st .estado { padding: 16px; border-radius: 10px; line-height: 1.6; }
        .st .estado.pendiente { background: #eef4ff; color: #1f4b8f; }
        .st .estado.aprobada { background: #e7f6ec; color: #1e6b3a; }
        .st .estado.rechazada { background: #fdecea; color: #9b2c22; }
        .st .error { color: #b42318; font-size: .9rem; margin-top: 6px; }

        /* Indicador de pasos */
        .st .pasos { display: flex; gap: 8px; margin: 4px 0 22px; padding: 0; list-style: none; flex-wrap: wrap; }
        .st .pasos li { flex: 1; min-width: 150px; display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; background: #f3f6fa; color: #65738a; font-weight: 600; font-size: 14px; }
        .st .pasos .num { width: 26px; height: 26px; border-radius: 50%; background: #d6deea; color: #4a5568; display: grid; place-items: center; font-size: 13px; flex: none; }
        .st .pasos li.actual { background: #eef4ff; color: #174ea6; }
        .st .pasos li.actual .num { background: #174ea6; color: #fff; }
        .st .pasos li.hecho { color: #1e6b3a; }
        .st .pasos li.hecho .num { background: #16804a; color: #fff; }

        /* Paso 1: tarjetas de tipo */
        .st .opciones { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .st .opcion { border: 2px solid #dbe3ee; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; gap: 10px; background: #fff; text-align: left; font: inherit; color: inherit; }
        .st .opcion.mia { border-color: #174ea6; box-shadow: 0 6px 20px #174ea61f; }
        .st .opcion.bloqueada { opacity: .55; background: #fafbfc; }
        .st .opcion .icono { font-size: 30px; }
        .st .opcion h3 { margin: 0; font-size: 1.05rem; color: #172b4d; }
        .st .opcion ul { margin: 0; padding-left: 18px; color: #4a5a70; font-size: 14px; line-height: 1.6; }
        .st .opcion .pie { margin-top: auto; font-size: 13px; color: #65738a; }
        .st .etiqueta { align-self: flex-start; font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 999px; background: #eef4ff; color: #174ea6; }

        /* Paso 2: carga */
        .st .instrucciones { background: #f4f7fb; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; color: #3a4a60; line-height: 1.6; font-size: 14.5px; }
        .st .instrucciones ol { margin: 6px 0 0; padding-left: 20px; }
        .st .datos { display: grid; grid-template-columns: max-content 1fr; gap: 6px 16px; background: #f4f7fb; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px; font-size: 14.5px; }
        .st .datos dt { color: #52657e; }
        .st .datos dd { margin: 0; font-weight: 600; color: #172b4d; }
        .st .doc { border: 1px dashed #c5d1e0; border-radius: 12px; padding: 14px 16px; margin-bottom: 12px; display: grid; gap: 6px; }
        .st .doc.listo { border-style: solid; border-color: #9fd3b4; background: #f6fbf8; }
        .st .doc-cab { display: flex; justify-content: space-between; gap: 10px; align-items: center; }
        .st .doc-cab strong { color: #172b4d; }
        .st .doc .ayuda { color: #65738a; font-size: 13.5px; }
        .st .doc input[type=file] { font: inherit; font-size: 14px; }
        .st .chip-ok { font-size: 12.5px; font-weight: 700; color: #1e6b3a; }
        .st .chip-pend { font-size: 12.5px; font-weight: 700; color: #8a5300; }
        .st .botones { display: flex; justify-content: space-between; gap: 10px; margin-top: 18px; flex-wrap: wrap; }

        /* Paso 3: revisión */
        .st .resumen { list-style: none; margin: 0 0 16px; padding: 0; display: grid; gap: 8px; }
        .st .resumen li { display: flex; justify-content: space-between; gap: 12px; background: #f6fbf8; border-radius: 8px; padding: 10px 12px; font-size: 14.5px; }
        .st .resumen li span:last-child { color: #4a5568; overflow-wrap: anywhere; text-align: right; }
        .st .check { display: flex; gap: 10px; align-items: flex-start; font-size: 14.5px; color: #243047; cursor: pointer; }
        .st .check input { margin-top: 3px; width: 18px; height: 18px; }

        /* Historial */
        .st table { width: 100%; border-collapse: collapse; font-size: .92rem; }
        .st th, .st td { text-align: left; padding: 9px 8px; border-bottom: 1px solid #e6ecf3; vertical-align: top; }
        .st th { color: #52657e; font-weight: 600; }
        .st .badge { padding: 3px 10px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
        .st .badge.pendiente { background: #eef4ff; color: #1f4b8f; }
        .st .badge.aprobada { background: #e7f6ec; color: #1e6b3a; }
        .st .badge.rechazada { background: #fdecea; color: #9b2c22; }

        @media (max-width: 760px) {
            .st .opciones { grid-template-columns: 1fr; }
            .st .historial { overflow-x: auto; }
        }
    </style>

    @php
        $ultima = $solicitudes[0] ?? null;
        $tipoPerfil = $perfil->academicDataCompleted() ? $perfil->registrationType : null;
        $opciones = [
            'DOCENTE' => ['Soy docente', '📘', ['Currículum vitae (PDF)', 'Código de docente (de tu perfil)']],
            'AUXILIAR' => ['Soy auxiliar', '🧑‍🏫', ['Currículum vitae (PDF)', 'Código de auxiliar (de tu perfil)', 'Documento de respaldo (PDF)']],
            'ESTUDIANTE' => ['Soy estudiante', '🎓', ['Carta de solicitud (PDF)', 'Currículum vitae (PDF)', 'Registro académico (PDF)', 'Historial académico (PDF)']],
        ];
        $instrucciones = [
            'DOCENTE' => 'Como docente, solo necesitas tu currículum. Tu código de docente se toma de tu perfil y el administrador lo verificará.',
            'AUXILIAR' => 'Como auxiliar, sube tu currículum y un documento que respalde tu auxiliatura (designación, certificado o constancia). Tu código de auxiliar se toma de tu perfil.',
            'ESTUDIANTE' => 'Como estudiante, el administrador evaluará tu rendimiento académico. Sube tu carta de solicitud, currículum, registro académico e historial académico.',
        ];
    @endphp

    <section class="panel">
        <h1>Solicitud de habilitación como tutor</h1>
        <p>Elige tu tipo de personal, carga la información solicitada y envíala a validar.</p>

        @if (session('mensaje')) <div class="msg-ok">{{ session('mensaje') }}</div> @endif
        @if (session('error')) <div class="alerta" style="margin-bottom: 14px;">{{ session('error') }}</div> @endif

        @if ($tienePendiente)

            <div class="estado pendiente">
                <strong>Tu solicitud está en revisión.</strong><br>
                La enviaste el {{ $ultima['fecha'] }}. Te avisaremos por notificación cuando el administrador la revise.
                Mientras tanto no puedes enviar otra.
            </div>

        @elseif ($tieneAprobada)

            <div class="estado aprobada">
                <strong>¡Tu solicitud fue aprobada!</strong><br>
                Ya estás habilitado como tutor. Encontrarás tus opciones en el área de tutor.
            </div>

        @elseif (! $tipoPerfil)

            <div class="alerta">
                <strong>Primero completa tus datos académicos.</strong><br>
                Para solicitar ser tutor debes registrar en <strong>Mi perfil</strong> tu facultad y tu tipo de registro
                (estudiante, auxiliar o docente) con su registro o código. Esa información se usa para validar tu solicitud.
                <br>
                <a class="btn" href="{{ route('perfil') }}">Ir a Mi perfil</a>
            </div>

        @else

            @if ($ultima && $ultima['estado'] === 'RECHAZADA' && $paso === 1)
                <div class="estado rechazada" style="margin-bottom: 16px;">
                    <strong>Tu última solicitud fue rechazada</strong> ({{ $ultima['fecha'] }}).<br>
                    Motivo: {{ $ultima['comentario_rechazo'] ?: 'Sin observación.' }}<br>
                    Corrige lo indicado y vuelve a enviarla.
                </div>
            @endif

            <ol class="pasos" aria-label="Pasos de la solicitud">
                @foreach ([1 => 'Elegir tipo', 2 => 'Cargar información', 3 => 'Enviar a validar'] as $n => $nombre)
                    <li class="{{ $paso === $n ? 'actual' : ($paso > $n ? 'hecho' : '') }}" @if ($paso === $n) aria-current="step" @endif>
                        <span class="num">{{ $paso > $n ? '✓' : $n }}</span> {{ $nombre }}
                    </li>
                @endforeach
            </ol>

            {{-- PASO 1: elegir una de las opciones --}}
            @if ($paso === 1)
                <h2>Elige una de las siguientes opciones</h2>
                <p>Cada opción pide documentos distintos. Solo se habilita la que coincide con tu perfil
                   (registrado como <strong>{{ mb_strtolower($tipoPerfil) }}</strong>).</p>

                <div class="opciones">
                    @foreach ($opciones as $valor => [$titulo, $icono, $requisitos])
                        @php $esMia = $valor === $tipoPerfil; @endphp
                        <div class="opcion {{ $esMia ? 'mia' : 'bloqueada' }}" wire:key="opcion-{{ $valor }}">
                            @if ($esMia) <span class="etiqueta">Tu perfil</span> @endif
                            <span class="icono" aria-hidden="true">{{ $icono }}</span>
                            <h3>{{ $titulo }}</h3>
                            <ul>@foreach ($requisitos as $r) <li>{{ $r }}</li> @endforeach</ul>
                            <div class="pie">
                                @if ($esMia)
                                    <button class="btn" wire:click="elegirTipo('{{ $valor }}')">Continuar</button>
                                @else
                                    No coincide con el tipo registrado en tu perfil.
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('tipo') <div class="error">{{ $message }}</div> @enderror
            @endif

            {{-- PASO 2: cargar información --}}
            @if ($paso === 2 && $tipo !== '')
                <h2>{{ $opciones[$tipo][0] }}: carga la información</h2>

                <div class="instrucciones">
                    {{ $instrucciones[$tipo] }}
                    <ol>
                        <li>Cada documento debe ser un <strong>PDF de hasta 5 MB</strong>.</li>
                        <li>Verifica que se lea con claridad; un documento ilegible puede ser motivo de rechazo.</li>
                        <li>Cuando todos estén cargados, continúa para revisarlos antes de enviar.</li>
                    </ol>
                </div>

                <dl class="datos">
                    <dt>Facultad</dt><dd>{{ $perfil->faculty }}</dd>
                    @if ($tipo !== 'DOCENTE') <dt>Registro universitario</dt><dd>{{ $perfil->studentRegistry }}</dd> @endif
                    @if ($tipo === 'AUXILIAR') <dt>Código de auxiliar</dt><dd>{{ $perfil->assistantRegistry }}</dd> @endif
                    @if ($tipo === 'DOCENTE') <dt>Código de docente</dt><dd>{{ $perfil->teacherCode }}</dd> @endif
                </dl>

                @foreach ($documentos[$tipo] as $propiedad => [$clave, $carpeta, $etiqueta, $ayuda])
                    @php $cargado = $this->{$propiedad} !== null && ! $errors->has($propiedad); @endphp
                    <div class="doc {{ $cargado ? 'listo' : '' }}" wire:key="doc-{{ $propiedad }}">
                        <div class="doc-cab">
                            <strong>{{ $etiqueta }}</strong>
                            @if ($cargado)
                                <span class="chip-ok">✓ {{ $this->{$propiedad}->getClientOriginalName() }}</span>
                            @else
                                <span class="chip-pend">Pendiente</span>
                            @endif
                        </div>
                        <span class="ayuda">{{ $ayuda }}</span>
                        <input type="file" wire:model="{{ $propiedad }}" accept=".pdf,application/pdf" aria-label="{{ $etiqueta }}">
                        <div wire:loading wire:target="{{ $propiedad }}" class="ayuda">Subiendo…</div>
                        @error($propiedad) <div class="error">{{ $message }}</div> @enderror
                    </div>
                @endforeach

                <div class="botones">
                    <button class="btn sec" wire:click="volver(1)">← Cambiar opción</button>
                    <button class="btn" wire:click="irARevision" wire:loading.attr="disabled">Revisar y continuar →</button>
                </div>
            @endif

            {{-- PASO 3: enviar a validar --}}
            @if ($paso === 3 && $tipo !== '')
                <h2>Revisa y envía a validar</h2>
                <p>Comprueba que los documentos sean los correctos. Al enviar, el administrador revisará tu solicitud.</p>

                <ul class="resumen">
                    <li><span>Tipo de personal</span><span>{{ $opciones[$tipo][0] }}</span></li>
                    @foreach ($documentos[$tipo] as $propiedad => [$clave, $carpeta, $etiqueta])
                        <li wire:key="res-{{ $propiedad }}"><span>✓ {{ $etiqueta }}</span><span>{{ $this->{$propiedad}?->getClientOriginalName() }}</span></li>
                    @endforeach
                </ul>

                <label class="check">
                    <input type="checkbox" wire:model="declaracion">
                    <span>Declaro que la información y los documentos presentados son verídicos.</span>
                </label>
                @error('declaracion') <div class="error">{{ $message }}</div> @enderror
                @foreach (array_keys($documentos[$tipo]) as $propiedad)
                    @error($propiedad) <div class="error">{{ $message }}</div> @enderror
                @endforeach

                <div class="botones">
                    <button class="btn sec" wire:click="volver(2)">← Volver a cargar</button>
                    <button class="btn ok-btn" wire:click="enviar" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="enviar">Enviar a validar</span>
                        <span wire:loading wire:target="enviar">Enviando…</span>
                    </button>
                </div>
            @endif

        @endif
    </section>

    @if (count($solicitudes) > 0)
        <section class="panel historial">
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
        </section>
    @endif
</div>
