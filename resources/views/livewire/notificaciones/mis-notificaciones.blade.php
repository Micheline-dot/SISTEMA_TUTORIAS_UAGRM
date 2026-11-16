<div class="notifs">
    <style>
        .notifs { max-width: 860px; }
        .notifs .barra { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
        .notifs .filtros { display: flex; gap: 8px; }
        .notifs .chip { padding: 8px 14px; border-radius: 999px; border: 1px solid #c5d1e0; background: white; cursor: pointer; font: inherit; font-size: 14px; color: #243047; }
        .notifs .chip.on { background: #174ea6; border-color: #174ea6; color: white; }
        .notifs .btn-link { background: none; border: 0; color: #174ea6; font: inherit; font-weight: 600; cursor: pointer; padding: 6px 0; }
        .notifs .lista { display: grid; gap: 10px; }
        .notifs .item { background: white; border-radius: 12px; padding: 16px 18px; box-shadow: 0 3px 14px rgba(25, 45, 75, .06); display: flex; gap: 14px; align-items: flex-start; border-left: 4px solid transparent; }
        .notifs .item.nueva { border-left-color: #174ea6; background: #f7faff; }
        .notifs .punto { width: 10px; height: 10px; border-radius: 50%; background: #174ea6; margin-top: 6px; flex: none; }
        .notifs .item:not(.nueva) .punto { background: #c5d1e0; }
        .notifs .cuerpo { flex: 1; min-width: 0; }
        .notifs .titulo { font-weight: 700; margin: 0 0 4px; }
        .notifs .mensaje { color: #4a5a70; margin: 0; line-height: 1.5; overflow-wrap: anywhere; }
        .notifs .meta { color: #8492a6; font-size: 13px; margin-top: 8px; display: flex; gap: 14px; flex-wrap: wrap; align-items: center; }
        .notifs .vacio { background: white; border-radius: 12px; padding: 28px; text-align: center; color: #718096; }
    </style>

    <div class="barra">
        <div class="filtros">
            <button class="chip {{ $soloNoLeidas ? '' : 'on' }}" wire:click="$set('soloNoLeidas', false)">Todas</button>
            <button class="chip {{ $soloNoLeidas ? 'on' : '' }}" wire:click="$set('soloNoLeidas', true)">No leídas ({{ $noLeidas }})</button>
        </div>
        @if ($noLeidas > 0)
            <button class="btn-link" wire:click="marcarTodas">Marcar todas como leídas</button>
        @endif
    </div>

    @if (count($notificaciones) === 0)
        <div class="vacio">
            {{ $soloNoLeidas ? 'No tienes notificaciones sin leer.' : 'Todavía no tienes notificaciones.' }}
        </div>
    @else
        <div class="lista">
            @foreach ($notificaciones as $n)
                <article class="item {{ $n->leida ? '' : 'nueva' }}" wire:key="notif-{{ $n->id }}">
                    <span class="punto" aria-hidden="true"></span>
                    <div class="cuerpo">
                        <p class="titulo">{{ $n->titulo }}</p>
                        <p class="mensaje">{{ $n->mensaje }}</p>
                        <div class="meta">
                            <span>{{ $n->fecha }}</span>
                            @if ($n->enlace)
                                <button class="btn-link" wire:click="abrir('{{ $n->id }}')">Ver detalle →</button>
                            @endif
                            @unless ($n->leida)
                                <button class="btn-link" wire:click="marcarLeida('{{ $n->id }}')">Marcar como leída</button>
                            @endunless
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
