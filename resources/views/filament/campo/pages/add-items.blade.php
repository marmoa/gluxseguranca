<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Formulário de adição --}}
        <x-filament::section>
            <x-slot name="heading">Selecionar OS e Item</x-slot>
            <form wire:submit="addItem">
                {{ $this->form }}
                <div class="mt-4 flex justify-end">
                    <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-right" size="lg">
                        Próximo: Preencher dados →
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Info da OS selecionada --}}
        @php $os = $this->getServiceOrderInfo() @endphp
        @if ($os)
            <x-filament::section>
                <x-slot name="heading">OS Selecionada</x-slot>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Número:</span>
                        <strong class="ml-1">#{{ $os->number }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Cliente:</span>
                        <strong class="ml-1">{{ $os->client?->name }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Status:</span>
                        <x-filament::badge color="{{ $os->status->color() ?? 'gray' }}" class="ml-1">
                            {{ $os->status->label() }}
                        </x-filament::badge>
                    </div>
                </div>
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
