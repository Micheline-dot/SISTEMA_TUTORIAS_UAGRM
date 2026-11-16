<div>
    <style>
        .attendance-header {
            background: linear-gradient(120deg, #173c68, #246b9c);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 22px;
        }

        .attendance-header h2 { margin: 0 0 8px; }
        .attendance-header p { margin: 0; color: #e0edf8; }

        .attendance-panel {
            background: white;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 3px 14px rgba(25,45,75,.06);
            overflow-x: auto;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .attendance-table th,
        .attendance-table td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid #e5eaf0;
        }

        .attendance-table th {
            color: #475569;
            font-size: 13px;
            background: #f8fafc;
        }

        .attendance-table td { color: #334155; }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .verified {
            color: #166534;
            background: #dcfce7;
        }

        .pending {
            color: #92400e;
            background: #fef3c7;
        }

        .btn-verify {
            border: 0;
            background: #16804a;
            color: white;
            padding: 9px 13px;
            border-radius: 7px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-verify:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .success, .alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .success { background: #dcfce7; color: #166534; }
        .alert { background: #fee2e2; color: #991b1b; }

        .empty {
            padding: 30px;
            text-align: center;
            color: #64748b;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #174ea6;
            text-decoration: none;
            font-weight: bold;
        }
    </style>

    <a class="back-link" href="{{ route('tutor.tutorias') }}">
        ← Volver a mis tutorías
    </a>

    @if (session()->has('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @error('general')
        <div class="alert">{{ $message }}</div>
    @enderror

    <section class="attendance-header">
        <h2>Gestión de asistencia</h2>
        <p>Consulta los estudiantes inscritos y verifica su participación en esta tutoría.</p>
    </section>

    <section class="attendance-panel">
        <h3>Estudiantes inscritos</h3>

        @if (count($estudiantes) > 0)
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Correo electrónico</th>
                        <th>Fecha de inscripción</th>
                        <th>Asistencia</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estudiantes as $estudiante)
                        <tr wire:key="asistencia-{{ $estudiante['id_inscripcion'] }}">
                            <td>{{ $estudiante['nombre_completo'] }}</td>
                            <td>{{ $estudiante['correo'] }}</td>
                            <td>{{ $estudiante['fecha_inscripcion'] }}</td>
                            <td>
                                @if ($estudiante['asistencia_verificada'])
                                    <span class="badge verified">Verificada</span>
                                @else
                                    <span class="badge pending">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if (!$estudiante['asistencia_verificada'])
                                    <button
                                        type="button"
                                        class="btn-verify"
                                        wire:click="verificar('{{ $estudiante['id_estudiante'] }}')"
                                        wire:confirm="¿Confirmas que este estudiante asistió a la tutoría?"
                                        wire:loading.attr="disabled"
                                        wire:target="verificar('{{ $estudiante['id_estudiante'] }}')"
                                    >
                                        Verificar asistencia
                                    </button>
                                @else
                                    <span class="badge verified">Registrada</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">
                <h3>No hay estudiantes inscritos</h3>
                <p>Cuando los estudiantes se inscriban, aparecerán en esta lista.</p>
            </div>
        @endif
    </section>
</div>