
<div class="max-w-3xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-800">
            Solicitud de habilitación como tutor
        </h1>

        <p class="text-gray-500 mt-2 mb-6">
            Selecciona tu tipo de personal y presenta los documentos requeridos.
        </p>

        @if (session('mensaje'))
            <div class="bg-green-100 text-green-800 p-3 rounded-lg mb-5">
                {{ session('mensaje') }}
            </div>
        @endif

        <form wire:submit="enviar" enctype="multipart/form-data" class="space-y-5">

            <div>
                <label class="block font-semibold mb-2">Tipo de personal</label>
                <select wire:model.live="tipoPersonal"
                        class="w-full border rounded-lg p-3">
                    <option value="">Selecciona una opción</option>
                    <option value="DOCENTE">Docente</option>
                    <option value="AUXILIAR">Auxiliar</option>
                    <option value="ESTUDIANTE">Estudiante</option>
                </select>
                @error('tipoPersonal') <span class="text-red-600">{{ $message }}</span> @enderror
            </div>

            @if ($tipoPersonal === 'DOCENTE')
                <div>
                    <label class="block font-semibold mb-2">Código DOC</label>
                    <input type="text" wire:model="codigoDoc"
                           class="w-full border rounded-lg p-3">
                    @error('codigoDoc') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            @endif

            @if ($tipoPersonal === 'AUXILIAR')
                <div>
                    <label class="block font-semibold mb-2">Código de auxiliar</label>
                    <input type="text" wire:model="codigoAuxiliar"
                           class="w-full border rounded-lg p-3">
                    @error('codigoAuxiliar') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">Documento de respaldo (PDF)</label>
                    <input type="file" wire:model="documentoRespaldo" accept=".pdf">
                    @error('documentoRespaldo') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            @endif

            @if ($tipoPersonal === 'ESTUDIANTE')
                <div>
                    <label class="block font-semibold mb-2">Carta de solicitud (PDF)</label>
                    <input type="file" wire:model="cartaSolicitud" accept=".pdf">
                    @error('cartaSolicitud') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">Registro académico (PDF)</label>
                    <input type="file" wire:model="registroAcademico" accept=".pdf">
                    @error('registroAcademico') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-semibold mb-2">Historial académico (PDF)</label>
                    <input type="file" wire:model="historialAcademico" accept=".pdf">
                    @error('historialAcademico') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            @endif

            @if (in_array($tipoPersonal, ['DOCENTE', 'AUXILIAR', 'ESTUDIANTE']))
                <div>
                    <label class="block font-semibold mb-2">Currículum vitae (PDF)</label>
                    <input type="file" wire:model="curriculum" accept=".pdf">
                    @error('curriculum') <span class="text-red-600">{{ $message }}</span> @enderror
                </div>
            @endif

            <button type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg">
                <span wire:loading.remove>Enviar solicitud</span>
                <span wire:loading>Enviando...</span>
            </button>
        </form>
    </div>
</div>
