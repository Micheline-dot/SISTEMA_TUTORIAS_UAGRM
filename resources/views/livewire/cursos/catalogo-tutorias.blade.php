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
        .filters {
            background: white;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 13px;
            align-items: end;
            box-shadow: 0 3px 14px rgba(25,45,75,.05);
        }
        .field label {
            display: block;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 7px;
        }
        .field input, .field select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            font: inherit;
        }
        .btn {
            border: 0;
            border-radius: 8px;
            padding: 12px 16px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary { background: #174ea6; color: white; }
        .btn-light { background: #e8edf4; color: #34445b; }
        .results-title { margin: 28px 0 17px; font-size: 20px; }
        .course-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 18px;
        }
        .course {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 14px rgba(25,45,75,.06);
        }
        .course h3 { margin: 10px 0; font-size: 19px; }
        .course p { color: #65738a; line-height: 1.5; }
        .tag {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 12px;
            font-weight: bold;
        }
        .tag-paid { background: #e8edf4; color: #34445b; }
        .meta { color: #65738a; font-size: 13px; margin-top: 12px; }
        .empty {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
        }
        @media(max-width: 900px) {
            .filters { grid-template-columns: 1fr 1fr; }
        }
        @media(max-width: 550px) {
            .filters { grid-template-columns: 1fr; }
        }
    </style>

    @if (session()->has('success'))
        <div style="background:#dcfce7;color:#166534;padding:14px;border-radius:8px;margin-bottom:18px;">
            {{ session('success') }}
            <a href="{{ route('estudiante.inscripciones') }}" style="font-weight:bold;margin-left:8px;">
                Ver mis inscripciones
            </a>
        </div>
    @endif

    @error('general')
        <div style="background:#fee2e2;color:#991b1b;padding:14px;border-radius:8px;margin-bottom:18px;">
            {{ $message }}
        </div>
    @enderror

    <section class="intro">
        <h2>Encuentra apoyo académico</h2>
        <p>
            Explora las tutorías publicadas por tutores de la UAGRM
            y encuentra el apoyo que necesitas.
        </p>
    </section>

    <section class="filters">
        <div class="field">
            <label for="search">Buscar tutoría</label>
            <input id="search" type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="Escribe un título o tema...">
        </div>

        <div class="field">
            <label for="modality">Modalidad</label>
            <select id="modality" wire:model.live="modality">
                <option value="">Todas</option>
                <option value="PRESENCIAL">Presencial</option>
                <option value="VIRTUAL">Virtual</option>
                <option value="HIBRIDA">Híbrida</option>
            </select>
        </div>

        <div class="field">
            <label for="free">Costo</label>
            <select id="free" wire:model.live="free">
                <option value="">Todos</option>
                <option value="1">Gratuitas</option>
                <option value="0">De pago</option>
            </select>
        </div>

        <button type="button" class="btn btn-light"
                wire:click="limpiarFiltros">
            Limpiar
        </button>
    </section>

    <h2 class="results-title">
        Tutorías disponibles ({{ count($tutorias) }})
    </h2>

    @if (count($tutorias) > 0)
        <section class="course-list">
            @foreach ($tutorias as $tutoria)
                <article class="course" wire:key="catalogo-{{ $tutoria->id }}">
                    @if ($tutoria->isFree === true)
                        <span class="tag">GRATUITA</span>
                    @elseif ($tutoria->isFree === false)
                        <span class="tag tag-paid">DE PAGO</span>
                    @endif

                    <h3>{{ $tutoria->title ?: 'Tutoría sin título' }}</h3>

                    <p>
                        {{ $tutoria->description ?: 'Sin descripción disponible.' }}
                    </p>

                    <div class="meta">
                        Tutor: <strong>{{ $tutoria->tutorName }}</strong>
                    </div>

                    <div class="meta">
                        Modalidad:
                        <strong>{{ $tutoria->modality?->value ?? 'No definida' }}</strong>
                    </div>

                    <div class="meta">
                        Cupos:
                        <strong>{{ $tutoria->totalSeats ?? 'No especificados' }}</strong>
                    </div>

                    <div style="margin-top: 18px;">
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="inscribirme('{{ $tutoria->id }}')"
                            wire:confirm="¿Deseas inscribirte en esta tutoría?"
                            wire:loading.attr="disabled"
                            wire:target="inscribirme('{{ $tutoria->id }}')"
                        >
                            Inscribirme
                        </button>
                    </div>
                </article>
            @endforeach
        </section>
    @else
        <section class="empty">
            <h3>No se encontraron tutorías</h3>
            <p>Prueba cambiando los filtros o utilizando otras palabras de búsqueda.</p>
        </section>
    @endif
</div>