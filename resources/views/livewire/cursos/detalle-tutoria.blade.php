<div class="dt">
    <style>
        .dt { display: grid; gap: 18px; max-width: 1000px; }
        .dt .volver { color: #174ea6; text-decoration: none; font-weight: 600; font-size: 14px; }
        .dt .layout { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 18px; align-items: start; }
        .dt .panel { background: white; border-radius: 12px; padding: 22px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .dt h2 { margin: 0 0 8px; font-size: 1.35rem; color: #172b4d; }
        .dt h3 { margin: 0 0 10px; font-size: 1.05rem; color: #172b4d; }
        .dt .etiquetas { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 10px; }
        .dt .tag { padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #e7f2fb; color: #1d5a86; }
        .dt .tag.gratis { background: #dcfce7; color: #166534; }
        .dt .tag.pago { background: #eceff3; color: #4a5568; }
        .dt .tag.estado { background: #fdecea; color: #9b2c22; }
        .dt .desc { color: #3a4a60; line-height: 1.65; white-space: pre-line; margin: 0; }
        .dt .datos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
        .dt .dato { background: #f4f7fb; border-radius: 10px; padding: 12px; }
        .dt .dato span { display: block; font-size: 12px; color: #65738a; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }
        .dt .dato strong { color: #172b4d; }
        .dt .tema { border-left: 3px solid #c5d1e0; padding: 8px 0 8px 14px; margin-bottom: 8px; }
        .dt .tema.hecho { border-left-color: #16804a; }
        .dt .tema-nombre { font-weight: 700; color: #172b4d; }
        .dt .sub { color: #8492a6; font-size: 13px; }
        .dt .recursos { margin: 6px 0 0; padding-left: 18px; font-size: 14px; color: #3a4a60; }
        .dt .recursos a { color: #174ea6; }
        .dt .candado { color: #8492a6; font-size: 13px; }
        .dt .inscripcion .cupos { font-size: 1.5rem; font-weight: 800; color: #172b4d; }
        .dt .btn { width: 100%; padding: 12px; border: 0; border-radius: 8px; background: #16804a; color: white; font: inherit; font-weight: 700; cursor: pointer; margin-top: 12px; }
        .dt .btn:disabled { background: #8d9db3; cursor: not-allowed; }
        .dt .btn.sec { background: #e9eef5; color: #243047; text-decoration: none; display: block; text-align: center; box-sizing: border-box; }
        .dt .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .dt .err { background: #fdecea; color: #9b2c22; border-radius: 8px; padding: 12px 14px; }
        .dt .info { background: #eef4ff; color: #1f4b8f; border-radius: 8px; padding: 10px 12px; font-size: 14px; margin-top: 12px; }
        .dt .tutor-cab { display: flex; gap: 12px; align-items: center; margin-bottom: 12px; }
        .dt .avatar { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; background: #e7f2fb; color: #21618e; display: grid; place-items: center; font-weight: 800; font-size: 22px; flex: none; }
        .dt .estrellas { color: #e0a100; letter-spacing: 1px; }
        .dt .bio { color: #3a4a60; font-size: 14px; line-height: 1.6; white-space: pre-line; }
        .dt .chips { display: flex; flex-wrap: wrap; gap: 6px; margin: 10px 0; }
        .dt .chip { background: #e7f2fb; color: #1d5a86; border-radius: 999px; padding: 4px 10px; font-size: 13px; }
        .dt .enlaces { margin: 0; padding-left: 18px; font-size: 14px; }
        .dt .enlaces a { color: #174ea6; overflow-wrap: anywhere; }
        @media (max-width: 860px) { .dt .layout { grid-template-columns: 1fr; } .dt .datos { grid-template-columns: 1fr 1fr; } }
    </style>

    @php
        $c = $d->course;
        $libres = $d->availableSeats();
        $fmt = fn (?string $f) => $f ? \Illuminate\Support\Carbon::parse($f)->format('d/m/Y') : '—';
    @endphp

    <a class="volver" href="{{ route('estudiante.catalogo') }}" wire:navigate>← Volver a la búsqueda</a>

    @if (session('ok')) <div class="ok">{{ session('ok') }}</div> @endif
    @error('general') <div class="err">{{ $message }}</div> @enderror

    <div class="layout">
        <div style="display: grid; gap: 18px;">
            <section class="panel">
                <div class="etiquetas">
                    @if ($c->categoryName) <span class="tag">{{ $c->categoryName }}</span> @endif
                    <span class="tag {{ $c->isFree ? 'gratis' : 'pago' }}">{{ $c->isFree ? 'Gratuita' : 'De pago' }}</span>
                    @if ($c->status->value !== 'PUBLICADO') <span class="tag estado">{{ ucfirst(mb_strtolower($c->status->value)) }}</span> @endif
                </div>
                <h2>{{ $c->title }}</h2>
                <p class="desc">{{ $c->description }}</p>

                <div class="datos">
                    <div class="dato"><span>Modalidad</span><strong>{{ $c->modality ? ucfirst(mb_strtolower($c->modality->value)) : '—' }}</strong></div>
                    <div class="dato"><span>Inicio</span><strong>{{ $fmt($c->startDate) }}</strong></div>
                    <div class="dato"><span>Finalización</span><strong>{{ $fmt($c->endDate) }}</strong></div>
                </div>
                @unless ($c->isFree)
                    <div class="info">Tutoría de pago: el pago se coordina directamente con el tutor, fuera del sistema.</div>
                @endunless
            </section>

            <section class="panel">
                <h3>Temario ({{ count($d->topics) }} tema{{ count($d->topics) === 1 ? '' : 's' }})</h3>
                @foreach ($d->topics as $n => $tema)
                    <div class="tema {{ $tema->isCompleted() ? 'hecho' : '' }}" wire:key="tema-{{ $tema->id }}">
                        <div class="tema-nombre">{{ $n + 1 }}. {{ $tema->name }}</div>
                        <div class="sub">{{ $tema->date ? $fmt($tema->date) : 'Fecha por definir' }}{{ $tema->isCompleted() ? ' · Completado' : '' }}</div>
                        @if (count($tema->resources) > 0)
                            @if ($d->canSeeResourceLinks())
                                <ul class="recursos">
                                    @foreach ($tema->resources as $r)
                                        <li><a href="{{ $r->url }}" target="_blank" rel="noopener noreferrer">{{ $r->name }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="candado">🔒 {{ count($tema->resources) }} recurso(s) disponible(s) al inscribirte</div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </section>
        </div>

        <div style="display: grid; gap: 18px;">
            <section class="panel inscripcion">
                @if ($libres !== null)
                    <div class="cupos">{{ $libres }} <span style="font-size: 14px; font-weight: 600; color: #65738a;">de {{ $c->totalSeats }} cupos libres</span></div>
                @endif

                @if ($d->viewerIsOwner)
                    <div class="info">Esta tutoría la impartes tú.</div>
                    <a class="btn sec" href="{{ route('tutor.tutoria', $c->id) }}">Gestionar tutoría</a>
                @elseif ($d->viewerIsEnrolled)
                    <div class="info">✓ Estás inscrito en esta tutoría.</div>
                    <a class="btn sec" href="{{ route('estudiante.inscripciones') }}">Ver mis inscripciones</a>
                @elseif ($d->viewerAlreadyParticipated)
                    <div class="info">Ya participaste en esta tutoría.</div>
                @elseif ($c->status->value !== 'PUBLICADO')
                    <div class="info">Esta tutoría ya no recibe inscripciones.</div>
                @else
                    <button class="btn" wire:click="inscribirme" @disabled($libres === 0)
                            wire:confirm="¿Deseas inscribirte en esta tutoría?" wire:loading.attr="disabled">
                        {{ $libres === 0 ? 'Sin cupos disponibles' : 'Inscribirme' }}
                    </button>
                @endif
            </section>

            <section class="panel">
                <h3>Sobre el tutor</h3>
                <div class="tutor-cab">
                    @if ($d->tutorHasPhoto)
                        <img class="avatar" src="{{ route('perfil.foto', ['userId' => $d->tutorId]) }}" alt="Foto de {{ $d->tutorName }}">
                    @else
                        <div class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($d->tutorName, 0, 1)) }}</div>
                    @endif
                    <div>
                        <strong>{{ $d->tutorName }}</strong>
                        <div class="sub">{{ $d->tutorFaculty ?? '' }}</div>
                        <div class="sub">
                            @if ($d->tutorRating !== null)
                                <span class="estrellas">{{ str_repeat('★', (int) round($d->tutorRating)) }}{{ str_repeat('☆', 5 - (int) round($d->tutorRating)) }}</span>
                                {{ number_format($d->tutorRating, 1) }} · {{ $d->tutorRatingCount }} reseña(s)
                            @else
                                Aún sin valoraciones
                            @endif
                        </div>
                    </div>
                </div>

                @if ($d->tutorBiography) <div class="bio">{{ $d->tutorBiography }}</div> @endif

                @if (count($d->tutorKnowledge) > 0)
                    <div class="chips">@foreach ($d->tutorKnowledge as $k) <span class="chip">{{ $k }}</span> @endforeach</div>
                @endif

                @if (count($d->tutorLinks) > 0)
                    <ul class="enlaces">
                        @foreach ($d->tutorLinks as $l)
                            <li><a href="{{ $l }}" target="_blank" rel="noopener noreferrer nofollow">{{ preg_replace('#^https?://(www\.)?#', '', $l) }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</div>
