<a href="{{ route('notificaciones') }}" wire:poll.60s class="campana" title="Notificaciones"
   aria-label="Notificaciones{{ $noLeidas > 0 ? ': '.$noLeidas.' sin leer' : '' }}"
   style="position: relative; display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #e7f2fb; color: #21618e; text-decoration: none; font-size: 19px;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
    </svg>
    @if ($noLeidas > 0)
        <span style="position: absolute; top: -4px; right: -4px; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 10px; background: #d92d20; color: #fff; font-size: 11px; font-weight: 700; display: grid; place-items: center;">
            {{ $noLeidas > 99 ? '99+' : $noLeidas }}
        </span>
    @endif
</a>
