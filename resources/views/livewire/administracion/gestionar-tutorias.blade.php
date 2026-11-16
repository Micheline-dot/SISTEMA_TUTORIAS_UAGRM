<div class="admin-page">

    <style>
        .admin-page {
            color: #243047;
            padding: 10px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .admin-header h2 {
            color: #142b4a;
            font-size: 25px;
            font-weight: 700;
            margin: 0;
        }

        .admin-header p {
            color: #718096;
            margin: 5px 0 0;
        }

        .admin-alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .admin-success {
            background: #e8f7ee;
            color: #17643a;
        }

        .admin-error {
            color: #c53030;
            font-size: 13px;
            margin-top: 6px;
        }

        .admin-filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            background: white;
            padding: 17px;
            border-radius: 10px;
            border: 1px solid #e5eaf1;
        }

        .admin-filters input,
        .admin-filters select {
            padding: 11px 13px;
            border: 1px solid #d8dee8;
            border-radius: 7px;
            min-width: 200px;
            flex: 1;
        }

        .admin-btn {
            border: none;
            padding: 10px 15px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-clear {
            background: #e9eef5;
            color: #243047;
        }

        .btn-cancel {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-close {
            background: #e9eef5;
            color: #243047;
        }

        .admin-table-container {
            overflow-x: auto;
            background: white;
            border: 1px solid #e5eaf1;
            border-radius: 10px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .admin-table th {
            background: #f3f6fa;
            color: #526078;
            padding: 14px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }

        .admin-table td {
            padding: 14px;
            border-top: 1px solid #edf0f5;
            font-size: 13px;
        }

        .admin-status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-publicado {
            background: #dcfce7;
            color: #166534;
        }

        .status-borrador {
            background: #fef3c7;
            color: #92400e;
        }

        .status-cancelado {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-finalizado {
            background: #e2e8f0;
            color: #475569;
        }

        .admin-empty {
            text-align: center;
            padding: 35px;
            color: #718096;
        }

        .admin-modal-bg {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .60);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            z-index: 9999;
        }

        .admin-modal {
            background: white;
            width: 100%;
            max-width: 480px;
            border-radius: 13px;
            padding: 25px;
            box-shadow: 0 15px 45px rgba(0,0,0,.2);
        }

        .admin-modal h3 {
            color: #142b4a;
            margin-top: 0;
        }

        .admin-modal textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            resize: vertical;
        }

        .admin-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }

        .btn-confirm {
            background: #b91c1c;
            color: white;
        }

        @media (max-width: 700px) {
            .admin-filters {
                flex-direction: column;
            }

            .admin-filters input,
            .admin-filters select {
                width: 100%;
            }
        }
                .btn-details {
            background: #dbeafe;
            color: #1d4ed8;
            margin-right: 5px;
        }

        .admin-detail-modal {
            background: white;
            width: 100%;
            max-width: 850px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 13px;
            padding: 25px;
            box-shadow: 0 15px 45px rgba(0,0,0,.2);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .detail-item {
            padding: 13px;
            background: #f5f7fb;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .detail-item strong {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .detail-item span {
            color: #243047;
            font-weight: 600;
        }

        .detail-section {
            color: #142b4a;
            font-size: 17px;
            font-weight: 700;
            margin: 22px 0 12px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        .detail-table th,
        .detail-table td {
            padding: 11px;
            border-bottom: 1px solid #e5eaf1;
            text-align: left;
            font-size: 12px;
        }

        .detail-table th {
            background: #f3f6fa;
            color: #526078;
        }

        @media (max-width: 600px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="admin-header">
        <div>
            <h2>Gestión de tutorías</h2>
            <p>Consulta y administra las tutorías registradas en el sistema.</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="admin-alert admin-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="admin-filters">

        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar por título o tutor..."
        >

        <select wire:model.live="status">
            <option value="">Todos los estados</option>
            <option value="BORRADOR">Borrador</option>
            <option value="PUBLICADO">Publicado</option>
            <option value="CANCELADO">Cancelado</option>
            <option value="FINALIZADO">Finalizado</option>
        </select>

        <button
            type="button"
            wire:click="limpiarFiltros"
            class="admin-btn btn-clear"
        >
            Limpiar filtros
        </button>

    </div>

    <div class="admin-table-container">
        <table class="admin-table">

            <thead>
                <tr>
                    <th>Tutoría</th>
                    <th>Tutor</th>
                    <th>Modalidad</th>
                    <th>Inscritos</th>
                    <th>Fecha de inicio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($tutorias as $tutoria)

                    <tr>
                        <td>
                            <strong>{{ $tutoria['titulo'] ?? 'Sin título' }}</strong>

                            <div style="color:#718096; margin-top:4px;">
                                {{ !empty($tutoria['es_gratuito']) ? 'Gratuita' : 'De pago' }}
                                @if (!empty($tutoria['cupos']))
                                    · {{ $tutoria['cupos'] }} cupos
                                @endif
                            </div>
                        </td>

                        <td>
                            {{ $tutoria['tutor_nombre'] ?? $tutoria['nombre_tutor'] ?? 'No disponible' }}

                            @if (!empty($tutoria['tutor_email']))
                                <div style="color:#718096; margin-top:4px;">
                                    {{ $tutoria['tutor_email'] }}
                                </div>
                            @endif
                        </td>

                        <td>
                            {{ $tutoria['modalidad'] ?? 'No definida' }}
                        </td>

                        <td>
                            {{ $tutoria['total_inscritos'] ?? 0 }}
                        </td>

                        <td>
                            @if (!empty($tutoria['fecha_inicio']))
                                {{ \Carbon\Carbon::parse($tutoria['fecha_inicio'])->format('d/m/Y') }}
                            @else
                                Sin fecha
                            @endif
                        </td>

                        <td>
                            @php
                                $estado = $tutoria['estado'] ?? '';
                            @endphp

                            <span class="admin-status
                                @if($estado === 'PUBLICADO') status-publicado
                                @elseif($estado === 'BORRADOR') status-borrador
                                @elseif($estado === 'CANCELADO') status-cancelado
                                @elseif($estado === 'FINALIZADO') status-finalizado
                                @endif
                            ">
                                {{ $estado }}
                            </span>
                        </td>


                        <td>
    <button
        type="button"
        wire:click="verDetalles('{{ $tutoria['id_curso'] }}')"
        class="admin-btn btn-details"
    >
        Ver detalles
    </button>

    @if (!in_array($estado, ['CANCELADO', 'FINALIZADO'], true))
        <button
            type="button"
            wire:click="abrirModalCancelacion('{{ $tutoria['id_curso'] }}')"
            class="admin-btn btn-cancel"
        >
            Cancelar
        </button>
    @else
        <span style="color:#718096;">
            Sin acciones
        </span>
    @endif
</td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="admin-empty">
                            No se encontraron tutorías.
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>
@if ($mostrarModalDetalles && $detalleTutoria)

    <div class="admin-modal-bg">

        <div class="admin-detail-modal">

            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">

                <div>
                    <h3 style="color:#142b4a; margin:0;">
                        Detalles de la tutoría
                    </h3>

                    <p style="color:#718096; margin-top:5px;">
                        Información general e historial de estudiantes.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="cerrarModalDetalles"
                    class="admin-btn btn-close"
                >
                    Cerrar
                </button>

            </div>

            <div class="detail-grid">

                <div class="detail-item">
                    <strong>Título</strong>
                    <span>{{ $detalleTutoria['titulo'] ?? 'No disponible' }}</span>
                </div>

                <div class="detail-item">
                    <strong>Tutor responsable</strong>
                    <span>{{ $detalleTutoria['nombre_tutor'] ?? 'No disponible' }}</span>
                </div>

                <div class="detail-item">
                    <strong>Correo del tutor</strong>
                    <span>{{ $detalleTutoria['correo_tutor'] ?? 'No disponible' }}</span>
                </div>

                <div class="detail-item">
                    <strong>Modalidad</strong>
                    <span>{{ $detalleTutoria['modalidad'] ?? 'No definida' }}</span>
                </div>

                <div class="detail-item">
                    <strong>Tipo de tutoría</strong>
                    <span>
                        {{ !empty($detalleTutoria['es_gratuito']) ? 'Gratuita' : 'De pago' }}
                    </span>
                </div>

                <div class="detail-item">
                    <strong>Cupos disponibles totales</strong>
                    <span>{{ $detalleTutoria['cupos'] ?? 'Sin límite definido' }}</span>
                </div>

                <div class="detail-item">
                    <strong>Fecha de inicio</strong>
                    <span>
                        {{ !empty($detalleTutoria['fecha_inicio'])
                            ? \Carbon\Carbon::parse($detalleTutoria['fecha_inicio'])->format('d/m/Y')
                            : 'Sin fecha' }}
                    </span>
                </div>

                <div class="detail-item">
                    <strong>Fecha de finalización</strong>
                    <span>
                        {{ !empty($detalleTutoria['fecha_finalizacion'])
                            ? \Carbon\Carbon::parse($detalleTutoria['fecha_finalizacion'])->format('d/m/Y')
                            : 'Sin fecha' }}
                    </span>
                </div>

                <div class="detail-item">
                    <strong>Estado</strong>
                    <span>{{ $detalleTutoria['estado'] ?? 'No disponible' }}</span>
                </div>

                <div class="detail-item">
                    <strong>Total de registros de inscripción</strong>
                    <span>{{ count($detalleTutoria['inscritos'] ?? []) }}</span>
                </div>

            </div>

            <h4 class="detail-section">
                Descripción de la tutoría
            </h4>

            <div class="detail-item">
                {{ $detalleTutoria['descripcion'] ?: 'No se registró una descripción.' }}
            </div>

            @if (($detalleTutoria['estado'] ?? '') === 'CANCELADO')

                <h4 class="detail-section">
                    Información de cancelación
                </h4>

                <div class="detail-item">
                    <strong>Motivo</strong>
                    <span>
                        {{ $detalleTutoria['motivo_cancelacion'] ?? 'No registrado' }}
                    </span>

                    <strong style="margin-top:12px;">
                        Fecha de cancelación
                    </strong>

                    <span>
                        {{ !empty($detalleTutoria['fecha_cancelacion'])
                            ? \Carbon\Carbon::parse($detalleTutoria['fecha_cancelacion'])->format('d/m/Y H:i')
                            : 'No registrada' }}
                    </span>
                </div>

            @endif

            <h4 class="detail-section">
                Historial de estudiantes inscritos
            </h4>

            <div class="admin-table-container">

                <table class="detail-table">

                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Correo</th>
                            <th>Inscripción</th>
                            <th>Asistencia</th>
                            <th>Fecha de inscripción</th>
                            <th>Motivo de cancelación</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($detalleTutoria['inscritos'] ?? [] as $inscrito)

                            <tr>
                                <td>
                                    {{ $inscrito['nombre_estudiante'] ?? 'No disponible' }}
                                </td>

                                <td>
                                    {{ $inscrito['correo_estudiante'] ?? 'No disponible' }}
                                </td>

                                <td>
                                    {{ $inscrito['estado'] ?? 'No disponible' }}
                                </td>

                                <td>
                                    {{ !empty($inscrito['asistencia_verificada'])
                                        ? 'Verificada'
                                        : 'No verificada' }}
                                </td>

                                <td>
                                    {{ !empty($inscrito['fecha_inscripcion'])
                                        ? \Carbon\Carbon::parse($inscrito['fecha_inscripcion'])->format('d/m/Y')
                                        : 'Sin fecha' }}
                                </td>

                                <td>
                                    {{ $inscrito['motivo_cancelacion'] ?? '—' }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="admin-empty">
                                    Esta tutoría todavía no tiene estudiantes inscritos.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endif
    @if ($mostrarModalCancelacion)

        <div class="admin-modal-bg">

            <div class="admin-modal">

                <h3>Cancelar tutoría</h3>

                <p>
                    ¿Estás seguro de cancelar esta tutoría?
                    Esta acción también cancelará las inscripciones activas
                    de los estudiantes.
                </p>

                <p>
                    <strong>Importante:</strong>
                    Se conservará el historial de inscripciones y las
                    asistencias verificadas.
                </p>

                <form wire:submit="cancelarTutoria">

                    <label for="motivoCancelacion">
                        Motivo de la cancelación
                    </label>

                    <textarea
                        id="motivoCancelacion"
                        wire:model="motivoCancelacion"
                        placeholder="Escribe el motivo por el cual se cancela la tutoría..."
                    ></textarea>

                    @error('motivoCancelacion')
                        <div class="admin-error">
                            {{ $message }}
                        </div>
                    @enderror

                    @error('cursoSeleccionado')
                        <div class="admin-error">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="admin-modal-actions">

                        <button
                            type="button"
                            wire:click="cerrarModalCancelacion"
                            class="admin-btn btn-close"
                        >
                            Volver
                        </button>

                        <button
                            type="submit"
                            class="admin-btn btn-confirm"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>
                                Confirmar cancelación
                            </span>

                            <span wire:loading>
                                Procesando...
                            </span>
                        </button>

                    </div>

                </form>

            </div>
        </div>

    @endif

</div>
