<div class="barra no-print">
    <label>Desde
        <input type="date" wire:model.live="desde" max="{{ $hasta ?: '' }}">
    </label>
    <label>Hasta
        <input type="date" wire:model.live="hasta" min="{{ $desde ?: '' }}">
    </label>
    @if ($desde !== '' || $hasta !== '')
        <button type="button" class="btn sec" wire:click="limpiar">Todo el historial</button>
    @endif
    <div class="acciones">
        <button type="button" class="btn sec" onclick="window.print()">Imprimir</button>
        <button type="button" class="btn" wire:click="exportarCsv" wire:loading.attr="disabled" wire:target="exportarCsv">Exportar CSV</button>
    </div>
</div>
@error('periodo') <div class="err">{{ $message }} Se muestra todo el historial.</div> @enderror
