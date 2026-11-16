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

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin: 25px 0;
        }

        .toolbar h2 { margin: 0; font-size: 20px; }

        .btn {
            border: 0;
            border-radius: 8px;
            padding: 11px 17px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-primary { background: #174ea6; color: white; }
        .btn-success { background: #16804a; color: white; }
        .btn-light { background: #e8edf4; color: #34445b; }

        .course-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }

        .course {
            background: white;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 14px rgba(25,45,75,.06);
        }

        .course h3 { margin: 0 0 12px; font-size: 18px; }
        .course p { color: #718096; line-height: 1.5; }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            background: #fff4d6;
            color: #8b6414;
            margin-bottom: 12px;
        }

        .status-published {
            background: #dcfce7;
            color: #166534;
        }

        .meta {
            color: #65738a;
            font-size: 13px;
            margin: 12px 0;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 17px;
        }

        .form-panel, .empty, .notice {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 3px 14px rgba(25,45,75,.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .field { margin-bottom: 16px; }
        .field.full { grid-column: 1 / -1; }

        .field label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            font-size: 14px;
        }

        .field input, .field textarea, .field select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
            background: white;
        }

        .field textarea { min-height: 110px; resize: vertical; }

        .check {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 12px 0;
        }

        .check input { width: auto; }

        .error {
            color: #b42318;
            font-size: 13px;
            margin-top: 5px;
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

        @media(max-width: 650px) {
            .form-grid { grid-template-columns: 1fr; }
            .field.full { grid-column: auto; }
            .toolbar { align-items: flex-start; flex-direction: column; }
        }
    </style>

    @if (session()->has('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @error('general')
        <div class="alert">{{ $message }}</div>
    @enderror

    <section class="intro">
        <h2>Gestiona tus tutorÃ­as acadÃ©micas</h2>
        <p>
            Crea tus tutorÃ­as, guarda los datos como borrador y publÃ­calas
            cuando estÃ©n listas para que los estudiantes puedan encontrarlas.
        </p>
    </section>

    <div class="toolbar">
        <h2>Mis tutorÃ­as registradas</h2>
        <button type="button" class="btn btn-primary" wire:click="nuevaTutoria">
            + Nueva tutorÃ­a
        </button>
    </div>

    @if ($showForm)
        <section class="form-panel">
            <h2>{{ $courseId ? 'Editar borrador' : 'Registrar nueva tutorÃ­a' }}</h2>
            <p>Completa los datos disponibles. Puedes guardar informaciÃ³n parcial como borrador.</p>

            <form wire:submit="guardarTutoria">
                <div class="form-grid">
                    <div class="field full">
                        <label for="title">TÃ­tulo de la tutorÃ­a</label>
                        <input id="title" type="text" wire:model="title"
                               placeholder="Ej. TutorÃ­a de CÃ¡lculo I">
                        @error('title') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field full">
                        <label for="description">DescripciÃ³n</label>
                        <textarea id="description" wire:model="description"
                                  placeholder="Describe los temas que desarrollarÃ¡s..."></textarea>
                        @error('description') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="modality">Modalidad</label>
                        <select id="modality" wire:model="modality">
                            <option value="">Seleccionar modalidad</option>
                            <option value="PRESENCIAL">Presencial</option>
                            <option value="VIRTUAL">Virtual</option>
                            <option value="HIBRIDA">HÃ­brida</option>
                        </select>
                        @error('modality') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="totalSeats">Cantidad de cupos</label>
                        <input id="totalSeats" type="number" min="1"
                               wire:model="totalSeats" placeholder="Ej. 20">
                        @error('totalSeats') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <label class="check">
                    <input type="checkbox" wire:model="isFree">
                    Esta tutorÃ­a es gratuita
                </label>

                <div class="actions">
                    <button type="submit" class="btn btn-primary">
                        Guardar borrador
                    </button>
                    <button type="button" class="btn btn-light" wire:click="cancelarFormulario">
                        Cancelar
                    </button>
                </div>
            </form>
        </section>
    @endif

    @if (count($tutorias) > 0)
        <section class="course-list">
            @foreach ($tutorias as $tutoria)
                <article class="course" wire:key="tutoria-{{ $tutoria->id }}">
                    <span class="status {{ $tutoria->status->value === 'PUBLICADO' ? 'status-published' : '' }}">
                        {{ $tutoria->status->value }}
                    </span>

                    <h3>{{ $tutoria->title ?: 'TutorÃ­a sin tÃ­tulo' }}</h3>

                    <div class="meta">
                        Modalidad:
                        {{ $tutoria->modality?->value ?? 'Sin definir' }}
                    </div>

                    <div class="meta">
                        CÃ³digo de tutorÃ­a: {{ $tutoria->id }}
                    </div>

                    <div class="actions">
                        @if ($tutoria->status->value === 'BORRADOR')
                            <button type="button" class="btn btn-light"
                                    wire:click="editarTutoria('{{ $tutoria->id }}')">
                                Editar
                            </button>

                            <button type="button" class="btn btn-success"
                                    wire:click="publicarTutoria('{{ $tutoria->id }}')"
                                    wire:confirm="Â¿EstÃ¡s seguro de publicar esta tutorÃ­a?">
                                Publicar
                            </button>
                        @endif

                        @if ($tutoria->status->value === 'PUBLICADO')
                            <a class="btn btn-primary"
                               href="{{ route('tutor.asistencia', $tutoria->id) }}">
                                Gestionar asistencia
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @else
        <section class="empty">
            <h3>AÃºn no tienes tutorÃ­as registradas</h3>
            <p>Selecciona Â«Nueva tutorÃ­aÂ» para comenzar a crear tu primera tutorÃ­a acadÃ©mica.</p>
        </section>
    @endif
</div>
