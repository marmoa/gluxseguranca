<x-filament-panels::page>
    @php
        $item = $this->getCurrentItem();
        $os   = $this->getCurrentServiceOrder();
    @endphp

    @if ($item && $os)
        <div class="space-y-4">

            {{-- Contexto --}}
            <div class="flex items-center justify-between p-3 rounded-lg bg-primary-50 dark:bg-primary-950 border border-primary-200 dark:border-primary-800 text-sm">
                <span class="text-primary-700 dark:text-primary-300">
                    <strong>OS #{{ $os->number }}</strong> — {{ $os->client?->name }}
                </span>
                <span class="font-semibold text-primary-800 dark:text-primary-200">
                    {{ $item->name }}
                    <x-filament::badge color="info" class="ml-2">
                        {{ $this->quantity }}x
                    </x-filament::badge>
                </span>
            </div>

            {{-- Formulário dinâmico --}}
            <x-filament::section>
                <x-slot name="heading">Dados da Inspeção</x-slot>
                <x-slot name="description">
                    Os valores serão replicados para todas as {{ $this->quantity }} unidade(s) deste lote.
                </x-slot>

                <form wire:submit="saveInspection">
                    {{ $this->form }}
                    <div class="mt-6 flex items-center justify-between gap-3">
                        <x-filament::button
                            wire:click="cancelAndGoBack"
                            color="gray"
                            icon="heroicon-o-arrow-left"
                        >
                            Cancelar
                        </x-filament::button>

                        <x-filament::button
                            type="submit"
                            color="success"
                            icon="heroicon-o-check"
                            size="lg"
                        >
                            Salvar e Continuar ✓
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

        </div>
    @else
        <x-filament::section>
            <p class="text-gray-500">
                Nenhum item pendente.
                <a href="{{ \App\Filament\Campo\Pages\AddItems::getUrl() }}" class="text-primary-600 underline">
                    Voltar ao início.
                </a>
            </p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
