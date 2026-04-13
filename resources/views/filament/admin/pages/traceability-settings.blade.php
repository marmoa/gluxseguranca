<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Faixas de Códigos de Rastreabilidade</x-slot>
            <x-slot name="description">
                Configure as faixas de códigos emitidos por número de dígitos. Os códigos são sequenciais e thread-safe.
            </x-slot>

            {{ $this->form }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
