<div class="cat">
    <style>
        .cat { display: grid; gap: 18px; }
        .cat .intro { background: linear-gradient(120deg, #173c68, #246b9c); color: white; border-radius: 15px; padding: 24px 26px; }
        .cat .intro h2 { margin: 0 0 6px; }
        .cat .intro p { margin: 0 0 14px; color: #e0edf8; line-height: 1.5; }
        .cat .buscador { position: relative; }
        .cat .buscador input { width: 100%; box-sizing: border-box; padding: 14px 16px 14px 44px; border: 0; border-radius: 10px; font: inherit; font-size: 15px; }
        .cat .buscador svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #65738a; }
        .cat .filtros { background: white; border-radius: 12px; padding: 16px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; align-items: end; }
        .cat .campo label { display: block; font-size: 12.5px; font-weight: 700; color: #52657e; margin-bottom: 5px; }
        .cat .campo select { width: 100%; padding: 9px 10px; border: 1px solid #c5d1e0; border-radius: 8px; font: inherit; font-size: 14px; background: white; }
        .cat .extra { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; grid-column: 1 / -1; }
        .cat .check { display: flex; gap: 8px; align-items: center; font-size: 14px; cursor: pointer; }
        .cat .btn-link { background: none; border: 0; color: #174ea6; font: inherit; font-weight: 600; cursor: pointer; }
        .cat .resultado { color: #52657e; font-size: 14px; }
        .cat .lista { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 16px; }
        .cat .card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); display: flex; flex-direction: column; gap: 9px; text-decoration: none; color: inherit; transition: transform .15s, box-shadow .15s; }
        .cat .card:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(25, 45, 75, .10); }
        .cat .etiquetas { display: flex; gap: 6px; flex-wrap: wrap; }
        .cat .tag { padding: 4px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #e7f2fb; color: #1d5a86; }
        .cat .tag.gratis { background: #dcfce7; color: #166534; }
        .cat .tag.pago { background: #eceff3; color: #4a5568; }
        .cat .tag.lleno { background: #fdecea; color: #9b2c22; }
        .cat .card h3 { margin: 0; font-size: 17px; color: #172b4d; }
        .cat .desc { color: #65738a; font-size: 14px; line-height: 1.5; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .cat .meta { color: #52657e; font-size: 13.5px; line-height: 1.7; }
        .cat .estrellas { color: #e0a100; letter-spacing: 1px; }
        .cat .sin-val { color: #8492a6; }
        .cat .ver { margin-top: auto; color: #174ea6; font-weight: 700; font-size: 14px; }
        .cat .vacio { background: white; border-radius: 12px; padding: 30px; text-align: center; color: #718096; }
        @media (max-width: 1000px) { .cat .filtros { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 600px) { .cat .filtros { grid-template-columns: 1fr 1fr; } }
    </style>

    <section class="intro">
        <h2>Encuentra apoyo académico</h2>
        <p>Busca por tema, nombre del tutor o categoría.</p>
        <div class="buscador">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" wire:model.live.debounce.400ms="search" maxlength="100"
                   placeholder="Ej. Cálculo, Ana Pérez, Programación…" aria-label="Buscar tutorías">
        </div>
    </section>

    <section class="filtros" aria-label="Filtros">
        <div class="campo">
            <label for="categoria">Categoría</label>
            <select id="categoria" wire:model.live="categoria">
                <option value="">Todas</option>
                @foreach ($categorias as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
            </select>
        </div>
        <div class="campo">
            <label for="modality">Modalidad</label>
            <select id="modality" wire:model.live="modality">
                <option value="">Todas</option>
                <option value="PRESENCIAL">Presencial</option>
                <option value="VIRTUAL">Virtual</option>
                <option value="HIBRIDA">Híbrida</option>
            </select>
        </div>
        <div class="campo">
            <label for="free">Costo</label>
            <select id="free" wire:model.live="free">
                <option value="">Todas</option>
                <option value="1">Gratuitas</option>
                <option value="0">De pago</option>
            </select>
        </div>
        <div class="campo">
            <label for="valoracion">Valoración del tutor</label>
            <select id="valoracion" wire:model.live="valoracion">
                <option value="">Cualquiera</option>
                <option value="3">3★ o más</option>
                <option value="4">4★ o más</option>
                <option value="5">5★</option>
            </select>
        </div>
        <div class="campo">
            <label for="orden">Ordenar por</label>
            <select id="orden" wire:model.live="orden">
                <option value="RECIENTES">Más recientes</option>
                <option value="PROXIMOS">Próximas a iniciar</option>
                <option value="VALORACION">Mejor valoradas</option>
            </select>
        </div>
        <div class="extra">
            <label class="check"><input type="checkbox" wire:model.live="conCupos"> Solo con cupos disponibles</label>
            @if ($hayFiltros)
                <button class="btn-link" wire:click="limpiarFiltros">Limpiar filtros</button>
            @endif
        </div>
    </section>

    <div class="resultado" wire:loading.remove>{{ count($tutorias) }} tutoría(s) encontrada(s)</div>
    <div class="resultado" wire:loading>Buscando…</div>

    @if (count($tutorias) === 0)
        <div class="vacio">
            @if ($hayFiltros)
                No encontramos tutorías con esos criterios. Prueba con otra búsqueda o
                <button class="btn-link" wire:click="limpiarFiltros">limpia los filtros</button>.
            @else
                Aún no hay tutorías publicadas.
            @endif
        </div>
    @else
        <div class="lista">
            @foreach ($tutorias as $t)
                @php $libres = $t->availableSeats(); @endphp
                <a class="card" href="{{ route('estudiante.tutoria', $t->id) }}" wire:navigate wire:key="catalogo-{{ $t->id }}">
                    <div class="etiquetas">
                        @if ($t->categoryName) <span class="tag">{{ $t->categoryName }}</span> @endif
                        <span class="tag {{ $t->isFree ? 'gratis' : 'pago' }}">{{ $t->isFree ? 'Gratuita' : 'De pago' }}</span>
                        @if ($libres === 0) <span class="tag lleno">Sin cupos</span> @endif
                    </div>
                    <h3>{{ $t->title ?: 'Tutoría sin título' }}</h3>
                    <p class="desc">{{ $t->description }}</p>
                    <div class="meta">
                        Tutor: <strong>{{ $t->tutorName }}</strong>
                        @if ($t->tutorRating !== null)
                            · <span class="estrellas" aria-label="{{ $t->tutorRating }} de 5">{{ str_repeat('★', (int) round($t->tutorRating)) }}{{ str_repeat('☆', 5 - (int) round($t->tutorRating)) }}</span>
                            {{ number_format($t->tutorRating, 1) }} ({{ $t->tutorRatingCount }})
                        @else
                            · <span class="sin-val">Sin valoraciones</span>
                        @endif
                        <br>
                        {{ $t->modality ? ucfirst(mb_strtolower($t->modality->value)) : '' }}
                        @if ($t->startDate)
                            · {{ \Illuminate\Support\Carbon::parse($t->startDate)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($t->endDate)->format('d/m/Y') }}
                        @endif
                        @if ($libres !== null) <br>{{ $libres }} de {{ $t->totalSeats }} cupos libres @endif
                    </div>
                    <span class="ver">Ver detalle →</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
