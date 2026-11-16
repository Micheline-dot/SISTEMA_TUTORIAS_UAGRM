
<div class="container" style="max-width: 1100px; margin: 30px auto; padding: 20px;">

    <h1 style="font-size: 26px; font-weight: bold; margin-bottom: 8px;">
        Gestión de solicitudes de tutor
    </h1>

    <p style="color: #666; margin-bottom: 25px;">
        Revisa las solicitudes de habilitación y decide si corresponde aprobarlas o rechazarlas.
    </p>

    @if (session()->has('mensaje'))
        <div style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
            {{ session('mensaje') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif

    @if (empty($solicitudes))
        <div style="background: #f3f4f6; padding: 30px; border-radius: 8px; text-align: center;">
            <h3 style="font-size: 18px; font-weight: bold;">
                No hay solicitudes pendientes
            </h3>
            <p style="color: #666; margin-top: 8px;">
                Cuando un usuario solicite habilitación como tutor, aparecerá aquí.
            </p>
        </div>
    @else

        <div style="display: grid; gap: 20px;">

            @foreach ($solicitudes as $solicitud)

                <div wire:key="solicitud-{{ $solicitud['id_solicitud'] }}"
                     style="border: 1px solid #ddd; border-radius: 10px; padding: 20px; background: white;">

                    <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">

                        <div>
                            <h2 style="font-size: 19px; font-weight: bold;">
                                Solicitud #{{ $solicitud['id_solicitud'] }}
                            </h2>

                            <p style="color: #555; margin-top: 5px;">
                                <strong>Solicitante:</strong>
                                {{ $solicitud['nombre_solicitante'] ?? 'Usuario no identificado' }}
                            </p>
                        </div>

                        <span style="background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 20px; height: fit-content;">
                            Pendiente
                        </span>

                    </div>

                    <hr style="margin: 15px 0; border-color: #eee;">

                    <p>
                        <strong>Tipo de personal:</strong>
                        {{ $solicitud['tipo_personal'] }}
                    </p>

                    @if (!empty($solicitud['codigo_doc']))
                        <p>
                            <strong>Código docente:</strong>
                            {{ $solicitud['codigo_doc'] }}
                        </p>
                    @endif

                    @if (!empty($solicitud['codigo_auxiliar']))
                        <p>
                            <strong>Código de auxiliar:</strong>
                            {{ $solicitud['codigo_auxiliar'] }}
                        </p>
                    @endif

                    @if (!empty($solicitud['registro_universitario']))
                        <p>
                            <strong>Registro universitario:</strong>
                            {{ $solicitud['registro_universitario'] }}
                        </p>
                    @endif

                    @if (!empty($solicitud['facultad_solicitante']))
                        <p>
                            <strong>Facultad:</strong>
                            {{ $solicitud['facultad_solicitante'] }}
                        </p>
                    @endif

                    @if (!empty($solicitud['correo_solicitante']))
                        <p>
                            <strong>Correo:</strong>
                            {{ $solicitud['correo_solicitante'] }}
                        </p>
                    @endif

                    <h3 style="font-weight: bold; margin-top: 18px; margin-bottom: 8px;">
                        Documentación presentada
                    </h3>

                    <ul style="padding-left: 22px; color: #444;">

                        @foreach ([
                            'curriculum' => 'Currículum vitae',
                            'documento_respaldo' => 'Documento de respaldo',
                            'carta_solicitud' => 'Carta de solicitud',
                            'registro_academico' => 'Registro académico',
                            'historial_academico' => 'Historial académico'
                        ] as $campo => $etiqueta)

                            @if (!empty($solicitud[$campo]))

                                <li style="margin-bottom: 9px;">
                                    <strong>{{ $etiqueta }}:</strong>

                                    <a
                                        href="{{ route('admin.solicitudes-tutor.documento', [
                                            'idSolicitud' => $solicitud['id_solicitud'],
                                            'tipoDocumento' => $campo
                                        ]) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        style="color: #1769aa; font-weight: bold; margin-left: 8px;"
                                    >
                                        Ver PDF
                                    </a>
                                </li>

                            @endif

                        @endforeach

                    </ul>

                    <p style="font-size: 13px; color: #777; margin-top: 12px;">
                        <strong>Fecha de solicitud:</strong>
                        {{ $solicitud['created_at'] ?? 'No disponible' }}
                    </p>

                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px;">

                        <button
                            type="button"
                            wire:click="aprobar({{ $solicitud['id_solicitud'] }})"
                            wire:confirm="¿Estás seguro de aprobar esta solicitud?"
                            wire:loading.attr="disabled"
                            style="background: #15803d; color: white; padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer;">
                            Aprobar solicitud
                        </button>

                        <button
                            type="button"
                            wire:click="seleccionarRechazo({{ $solicitud['id_solicitud'] }})"
                            style="background: #b91c1c; color: white; padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer;">
                            Rechazar solicitud
                        </button>

                    </div>

                    @if ($solicitudSeleccionada === (int) $solicitud['id_solicitud'])

                        <div style="margin-top: 20px; padding: 18px; background: #f9fafb; border-radius: 8px;">

                            <h3 style="font-weight: bold; margin-bottom: 10px;">
                                Motivo del rechazo
                            </h3>

                            <form wire:submit="rechazar">

                                <textarea
                                    wire:model="observacion"
                                    rows="4"
                                    placeholder="Escribe el motivo por el cual se rechaza la solicitud..."
                                    style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px;"
                                ></textarea>

                                @error('observacion')
                                    <p style="color: #b91c1c; font-size: 13px; margin-top: 5px;">
                                        {{ $message }}
                                    </p>
                                @enderror

                                <div style="display: flex; gap: 10px; margin-top: 12px;">

                                    <button
                                        type="submit"
                                        wire:loading.attr="disabled"
                                        style="background: #b91c1c; color: white; padding: 10px 18px; border: none; border-radius: 6px;">
                                        Confirmar rechazo
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="cancelarRechazo"
                                        style="background: #6b7280; color: white; padding: 10px 18px; border: none; border-radius: 6px;">
                                        Cancelar
                                    </button>

                                </div>

                            </form>

                        </div>

                    @endif

                </div>

            @endforeach

        </div>

    @endif

</div>
