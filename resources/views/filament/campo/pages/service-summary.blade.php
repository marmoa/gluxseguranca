<x-filament-panels::page>
    @php $os = $this->getServiceOrder() @endphp

    @if ($os)
        <div class="space-y-6">

            {{-- Cabeçalho da OS --}}
            <x-filament::section>
                <x-slot name="heading">OS #{{ $os->number }}</x-slot>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Cliente:</span>
                        <strong class="ml-1">{{ $os->client?->name }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Data:</span>
                        <strong class="ml-1">{{ now()->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </x-filament::section>

            {{-- Totais gerais --}}
            <div class="grid grid-cols-3 gap-4">
                <x-filament::section>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-success-600 dark:text-success-400">
                            {{ $this->getTotalApproved() }}
                        </div>
                        <div class="text-sm text-gray-500 mt-1">✅ Aprovados</div>
                    </div>
                </x-filament::section>
                <x-filament::section>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-danger-600 dark:text-danger-400">
                            {{ $this->getTotalRejected() }}
                        </div>
                        <div class="text-sm text-gray-500 mt-1">❌ Reprovados</div>
                    </div>
                </x-filament::section>
                <x-filament::section>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-warning-600 dark:text-warning-400">
                            {{ $this->getTotalPending() }}
                        </div>
                        <div class="text-sm text-gray-500 mt-1">⏳ Pendentes</div>
                    </div>
                </x-filament::section>
            </div>

            {{-- Detalhes por item --}}
            @if (count($this->summary))
                <x-filament::section>
                    <x-slot name="heading">Detalhes por Item</x-slot>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-2 text-left">Item</th>
                                    <th class="px-4 py-2 text-center">Total</th>
                                    <th class="px-4 py-2 text-center">✅ Aprovados</th>
                                    <th class="px-4 py-2 text-center">❌ Reprovados</th>
                                    <th class="px-4 py-2 text-center">⏳ Pendentes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->summary as $row)
                                    <tr class="border-t dark:border-gray-700">
                                        <td class="px-4 py-2 font-medium">{{ $row['item']->name }}</td>
                                        <td class="px-4 py-2 text-center">{{ $row['quantity'] }}</td>
                                        <td class="px-4 py-2 text-center font-bold text-success-600 dark:text-success-400">{{ $row['approved'] }}</td>
                                        <td class="px-4 py-2 text-center font-bold text-danger-600 dark:text-danger-400">{{ $row['rejected'] }}</td>
                                        <td class="px-4 py-2 text-center font-bold text-warning-600 dark:text-warning-400">{{ $row['pending'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            @endif

        </div>
    @else
        <x-filament::section>
            <p class="text-gray-500">
                Nenhuma OS ativa.
                <a href="{{ \App\Filament\Campo\Pages\AddItems::getUrl() }}" class="text-primary-600 underline">
                    Iniciar novo serviço.
                </a>
            </p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
