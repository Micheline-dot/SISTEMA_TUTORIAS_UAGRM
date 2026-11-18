{{-- Tarjeta de tutoría publicada ($t: PublishedCourseSummary). $mostrarTutor = false en el perfil del tutor. --}}
@php
    $libres = $t->availableSeats();
    $mostrarTutor ??= true;
    $detalle = route('estudiante.tutoria', $t->id);
@endphp
<article class="tt-card" wire:key="tarjeta-{{ $t->id }}">
    <a class="tt-portada" href="{{ $detalle }}" wire:navigate aria-label="Ver {{ $t->title }}">
        @if ($t->hasCover)
            <img src="{{ route('tutoria.portada', ['courseId' => $t->id]) }}" alt="" loading="lazy">
        @else
            <span class="tt-sin">{{ $t->title ?: 'Tutoría' }}</span>
        @endif
        <span class="tt-etq">
            @if ($t->categoryName) <span class="tt-tag">{{ $t->categoryName }}</span> @endif
            <span class="tt-tag {{ $t->isFree ? 'gratis' : 'pago' }}">{{ $t->isFree ? 'Gratuita' : 'De pago' }}</span>
            @if ($libres === 0) <span class="tt-tag lleno">Sin cupos</span> @endif
        </span>
    </a>

    <div class="tt-cuerpo">
        <h3><a href="{{ $detalle }}" wire:navigate>{{ $t->title ?: 'Tutoría sin título' }}</a></h3>
        <p class="tt-desc">{{ $t->description }}</p>
        <div class="tt-meta">
            {{ $t->modality ? ucfirst(mb_strtolower($t->modality->value)) : '' }}
            @if ($t->startDate)
                · {{ \Illuminate\Support\Carbon::parse($t->startDate)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($t->endDate)->format('d/m/Y') }}
            @endif
            @if ($libres !== null) <br>{{ $libres }} de {{ $t->totalSeats }} cupos libres @endif
        </div>

        @if ($mostrarTutor)
            <a class="tt-tutor" href="{{ route('estudiante.tutor', ['tutorId' => $t->tutorId]) }}" wire:navigate title="Ver perfil del tutor">
                @if ($t->tutorHasPhoto)
                    <img class="tt-avatar" src="{{ route('perfil.foto', ['userId' => $t->tutorId]) }}" alt="" loading="lazy">
                @else
                    <span class="tt-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($t->tutorName, 0, 1)) }}</span>
                @endif
                <span>
                    <strong>{{ $t->tutorName }}</strong>
                    <small>
                        @if ($t->tutorRating !== null)
                            <span class="estrellas">★</span> {{ number_format($t->tutorRating, 1) }} ({{ $t->tutorRatingCount }}) ·
                        @endif
                        Ver perfil
                    </small>
                </span>
            </a>
        @endif
    </div>
</article>
