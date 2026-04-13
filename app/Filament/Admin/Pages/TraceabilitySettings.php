<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Models\TraceabilitySetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TraceabilitySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Rastreabilidade';

    protected static ?string $title = 'Configurações de Rastreabilidade';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.admin.pages.traceability-settings';

    /** Estado do formulário */
    public array $data = [];

    public function mount(): void
    {
        $formData = [];
        foreach (TraceabilitySetting::orderBy('digit_count')->get() as $setting) {
            $formData["range_start_{$setting->digit_count}"] = $setting->range_start;
            $formData["range_end_{$setting->digit_count}"]   = $setting->range_end;
            $formData["last_used_{$setting->digit_count}"]   = $setting->last_used;
        }

        $this->form->fill($formData);
    }

    public function form(Form $form): Form
    {
        $sections = [];

        foreach (TraceabilitySetting::orderBy('digit_count')->get() as $setting) {
            $dc = $setting->digit_count;

            $sections[] = Section::make("{$dc} Dígitos — {$setting->label}")
                ->description("Disponíveis: {$setting->remainingCodes()} | Uso: {$setting->usagePercent()}%")
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make("range_start_{$dc}")
                            ->label('Início da Faixa')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        TextInput::make("range_end_{$dc}")
                            ->label('Fim da Faixa')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        TextInput::make("last_used_{$dc}")
                            ->label('Último Código Gerado')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                ]);
        }

        return $form
            ->schema($sections)
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar Configurações')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        foreach (TraceabilitySetting::orderBy('digit_count')->get() as $setting) {
            $dc       = $setting->digit_count;
            $newStart = (int) ($formData["range_start_{$dc}"] ?? $setting->range_start);
            $newEnd   = (int) ($formData["range_end_{$dc}"] ?? $setting->range_end);

            if ($setting->last_used > 0 && $newStart > $setting->last_used) {
                Notification::make()
                    ->title("Faixa {$dc} dígitos: o início ({$newStart}) não pode ser maior que o último código gerado ({$setting->last_used}).")
                    ->danger()
                    ->send();
                return;
            }

            if ($newEnd <= $newStart) {
                Notification::make()
                    ->title("Faixa {$dc} dígitos: o fim deve ser maior que o início.")
                    ->danger()
                    ->send();
                return;
            }

            $setting->update([
                'range_start' => $newStart,
                'range_end'   => $newEnd,
            ]);
        }

        Notification::make()
            ->title('Configurações de rastreabilidade salvas com sucesso.')
            ->success()
            ->send();

        // Recarrega os valores exibidos
        $this->mount();
    }
}
