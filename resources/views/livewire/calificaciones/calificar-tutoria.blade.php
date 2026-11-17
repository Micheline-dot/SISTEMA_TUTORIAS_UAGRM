<div class="cal">
    <style>
        .cal { max-width: 760px; display: grid; gap: 16px; }
        .cal .volver { color: #174ea6; text-decoration: none; font-weight: 600; font-size: 14px; }
        .cal .panel { background: white; border-radius: 14px; padding: 24px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); }
        .cal h2 { margin: 0 0 4px; font-size: 1.25rem; color: #172b4d; }
        .cal .sub { color: #65738a; font-size: 14px; margin: 0 0 16px; line-height: 1.5; }
        .cal .pasos { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; }
        .cal .paso { flex: 1; min-width: 140px; padding: 9px 12px; border-radius: 10px; background: #f3f6fa; color: #65738a; font-weight: 600; font-size: 14px; }
        .cal .paso.actual { background: #eef4ff; color: #174ea6; }
        .cal .paso.hecho { color: #1e6b3a; }
        .cal .estrellas { display: flex; gap: 6px; }
        .cal .estrella { background: none; border: 0; font-size: 40px; line-height: 1; cursor: pointer; color: #d6deea; padding: 0 2px; transition: transform .1s; }
        .cal .estrella.on { color: #e0a100; }
        .cal .estrella:hover { transform: scale(1.12); }
        .cal .estrella:focus-visible { outline: 2px solid #174ea6; border-radius: 6px; }
        .cal .leyenda { color: #52657e; font-size: 14px; margin-top: 6px; min-height: 20px; }
        .cal label.t { display: block; font-weight: 600; font-size: 14px; margin: 16px 0 6px; }
        .cal textarea { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; }
        .cal .contador { color: #8492a6; font-size: 12px; text-align: right; }
        .cal .btn { padding: 11px 20px; border: 0; border-radius: 8px; background: #174ea6; color: white; font: inherit; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
        .cal .btn.sec { background: #e9eef5; color: #243047; }
        .cal .pregunta { border-top: 1px solid #eef2f7; padding: 14px 0; }
        .cal .pregunta:first-of-type { border-top: 0; }
        .cal .pregunta p { margin: 0 0 8px; font-weight: 600; color: #172b4d; }
        .cal .opciones { display: flex; gap: 6px; flex-wrap: wrap; }
        .cal .opcion { border: 1px solid #c5d1e0; border-radius: 8px; padding: 8px 12px; cursor: pointer; font-size: 13.5px; display: inline-flex; gap: 6px; align-items: center; }
        .cal .opcion.on { border-color: #174ea6; background: #eef4ff; color: #174ea6; font-weight: 600; }
        .cal .opcion input { position: absolute; opacity: 0; pointer-events: none; }
        .cal .opcion:focus-within { outline: 2px solid #9bb5dc; }
        .cal .ok { background: #e8f6ee; color: #1d6b3f; border-radius: 8px; padding: 12px 14px; }
        .cal .err { background: #fdecea; color: #9b2c22; border-radius: 8px; padding: 12px 14px; }
        .cal .info { background: #fff4e5; color: #8a5300; border-radius: 10px; padding: 14px 16px; line-height: 1.55; }
        .cal .error { color: #b42318; font-size: 13px; margin-top: 4px; }
        .cal .fin { text-align: center; padding: 10px 0; }
        .cal .fin .grande { font-size: 44px; color: #e0a100; letter-spacing: 2px; }
    </style>

    @php
        $rating = $estado['rating'];
        $paso = $rating === null ? 1 : ($rating['hasForm'] ? 3 : 2);
        $textos = [1 => 'Muy malo', 2 => 'Malo', 3 => 'Regular', 4 => 'Bueno', 5 => 'Excelente'];
    @endphp

    <a class="volver" href="{{ route('estudiante.inscripciones') }}" wire:navigate>← Mis inscripciones</a>

    @if (session('ok')) <div class="ok">{{ session('ok') }}</div> @endif
    @error('general') <div class="err">{{ $message }}</div> @enderror

    <section class="panel">
        <h2>{{ $estado['courseTitle'] }}</h2>

        @if ($estado['reason'])
            <div class="info">{{ $estado['reason'] }}</div>
        @else
            <div class="pasos">
                <div class="paso {{ $paso === 1 ? 'actual' : 'hecho' }}">{{ $paso > 1 ? '✓' : '1.' }} Calificar al tutor</div>
                <div class="paso {{ $paso === 2 ? 'actual' : ($paso > 2 ? 'hecho' : '') }}">{{ $paso > 2 ? '✓' : '2.' }} Encuesta</div>
            </div>

            @if ($paso === 1)
                <p class="sub">¿Cómo fue tu experiencia con el tutor? Tu calificación y reseña serán visibles para otros estudiantes.</p>
                <form wire:submit="enviarCalificacion">
                    <div class="estrellas" role="radiogroup" aria-label="Calificación">
                        @for ($s = 1; $s <= 5; $s++)
                            <button type="button" class="estrella {{ $puntuacion >= $s ? 'on' : '' }}" wire:click="elegir({{ $s }})"
                                    role="radio" aria-checked="{{ $puntuacion === $s ? 'true' : 'false' }}" aria-label="{{ $s }} estrella{{ $s > 1 ? 's' : '' }}">★</button>
                        @endfor
                    </div>
                    <div class="leyenda">{{ $puntuacion ? $puntuacion.' de 5 · '.$textos[$puntuacion] : 'Elige de 1 a 5 estrellas' }}</div>
                    @error('puntuacion') <div class="error">{{ $message }}</div> @enderror

                    <label class="t" for="comentario">Tu reseña</label>
                    <textarea id="comentario" rows="4" wire:model.live.debounce.400ms="comentario" maxlength="1000"
                              placeholder="¿Qué te gustó? ¿El tutor explicó bien? ¿Lo recomendarías?"></textarea>
                    <div class="contador">{{ mb_strlen($comentario) }}/1000</div>
                    @error('comentario') <div class="error">{{ $message }}</div> @enderror

                    <div style="margin-top: 14px;"><button class="btn" type="submit" wire:loading.attr="disabled">Enviar calificación</button></div>
                </form>
            @elseif ($paso === 2)
                <p class="sub">Calificaste con {{ $rating['score'] }} de 5 estrellas. Responde esta breve encuesta para ayudar al tutor a mejorar.</p>
                <form wire:submit="enviarEncuesta">
                    @foreach ($preguntas as $clave => $q)
                        <div class="pregunta" wire:key="preg-{{ $clave }}">
                            <p>{{ $q['text'] }}</p>
                            @if ($q['type'] === 'ESCALA')
                                <div class="opciones" role="radiogroup">
                                    @foreach ($escala as $valor => $texto)
                                        <label class="opcion {{ (string) ($respuestas[$clave] ?? '') === (string) $valor ? 'on' : '' }}">
                                            <input type="radio" wire:model.live="respuestas.{{ $clave }}" value="{{ $valor }}"> {{ $valor }} · {{ $texto }}
                                        </label>
                                    @endforeach
                                </div>
                            @elseif ($q['type'] === 'SI_NO')
                                <div class="opciones" role="radiogroup">
                                    @foreach (['SI' => 'Sí', 'NO' => 'No'] as $valor => $texto)
                                        <label class="opcion {{ ($respuestas[$clave] ?? '') === $valor ? 'on' : '' }}">
                                            <input type="radio" wire:model.live="respuestas.{{ $clave }}" value="{{ $valor }}"> {{ $texto }}
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <textarea rows="3" wire:model="respuestas.{{ $clave }}" maxlength="1000" aria-label="{{ $q['text'] }}"></textarea>
                            @endif
                            @error('respuestas.'.$clave) <div class="error">{{ $message }}</div> @enderror
                        </div>
                    @endforeach
                    <button class="btn" type="submit" wire:loading.attr="disabled">Enviar encuesta</button>
                </form>
            @else
                <div class="fin">
                    <div class="grande">{{ str_repeat('★', $rating['score']) }}{{ str_repeat('☆', 5 - $rating['score']) }}</div>
                    <p class="sub" style="margin: 8px 0 14px;">Ya calificaste esta tutoría y respondiste la encuesta. ¡Gracias!</p>
                    @if ($rating['comment']) <p style="color: #3a4a60; font-style: italic;">"{{ $rating['comment'] }}"</p> @endif
                    <a class="btn sec" href="{{ route('estudiante.inscripciones') }}" wire:navigate>Volver a mis inscripciones</a>
                </div>
            @endif
        @endif
    </section>
</div>
