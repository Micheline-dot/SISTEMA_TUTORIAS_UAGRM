<div>
    <style>
        .intro {
            background: linear-gradient(120deg, #173c68, #246b9c);
            color: white;
            border-radius: 15px;
            padding: 27px;
            margin-bottom: 25px;
        }

        .intro h2 { margin: 0 0 8px; }
        .intro p { margin: 0; color: #e0edf8; line-height: 1.5; }

        .enrollment-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 18px;
        }

        .enrollment {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 14px rgba(25,45,75,.06);
        }

        .enrollment h3 { margin: 0 0 12px; }
        .meta { color: #65738a; font-size: 14px; margin: 12px 0; }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 12px;
            font-weight: bold;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 11px 16px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-danger { background: #b42318; color: white; }

        .attendance-status {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .attendance-verified {
            background: #dcfce7;
            color: #166534;
        }

        .attendance-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .attendance-section {
            margin: 15px 0;
        }

        .attendance-label {
            display: block;
            color: #65738a;
            font-size: 14px;
            margin-bottom: 7px;
        }

        .empty, .notice {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .alert {
            background: #fee2e2;
            color: #991b1b;
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }
    </style>

    @if (session()->has('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @error('general')
        <div class="alert">{{ $message }}</div>
    @enderror

    <section class="intro">
        <h2>Mis inscripciones</h2>
        <p>
            Consulta las tutorías en las que te has inscrito
            y administra tus reservas de cupos.
        </p>
    </section>

    @if (count($inscripciones) > 0)
        <section class="enrollment-list">
            @foreach ($inscripciones as $inscripcion)
                <article class="enrollment" wire:key="inscripcion-{{ $inscripcion['id_inscripcion'] }}">
                    <span class="status {{ $inscripcion['estado'] === 'CANCELADO' ? 'status-cancelled' : '' }}">
                        {{ $inscripcion['estado'] }}
                    </span>

                    <h3>
                        {{ $inscripcion['curso']['titulo'] ?? 'Tutoría sin título' }}
                    </h3>

                    <p>
                        {{ $inscripcion['curso']['descripcion'] ?? 'Sin descripción disponible.' }}
                    </p>

                    <div class="meta">
                        Tutor:
                        <strong>
                            {{ $inscripcion['curso']['tutor']['nombre_completo'] ?? 'No disponible' }}
                        </strong>
                    </div>

                    <div class="meta">
                        Fecha de inscripción:
                        <strong>
                            {{ $inscripcion['fecha_inscripcion'] ?? 'No disponible' }}
                        </strong>
                    </div>

                    @if ($inscripcion['estado'] === 'INSCRITO')
                        <div class="attendance-section">
                            <span class="attendance-label">Estado de asistencia:</span>

                            @if ((bool) ($inscripcion['asistencia_verificada'] ?? false))
                                <span class="attendance-status attendance-verified">
                                    ✓ Asistencia verificada
                                </span>
                            @else
                                <span class="attendance-status attendance-pending">
                                    Pendiente de verificación
                                </span>
                            @endif
                        </div>
                    @endif
                    @if ($inscripcion['estado'] === 'INSCRITO')
                        <button
                            type="button"
                            class="btn btn-danger"
                            wire:click="cancelarInscripcion('{{ $inscripcion['id_curso'] }}')"
                            wire:confirm="¿Estás seguro de cancelar esta inscripción? Se liberará tu cupo."
                        >
                            Cancelar inscripción
                        </button>
                    @else
                        <p class="meta">Esta inscripción ya fue cancelada.</p>
                    @endif
                </article>
            @endforeach
        </section>
    @else
        <section class="empty">
            <h3>Aún no tienes inscripciones</h3>
            <p>Busca una tutoría disponible y realiza tu primera inscripción.</p>
            <a href="{{ route('estudiante.catalogo') }}">
                Buscar tutorías
            </a>
        </section>
    @endif
</div>