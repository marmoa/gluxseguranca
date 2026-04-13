<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users;

use App\Filament\Admin\Resources\Users\Pages\CreateUser;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\Client;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    protected static string|\UnitEnum|null $navigationGroup = 'Administração';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados Pessoais')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome completo')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(150),
                        TextInput::make('login')
                            ->label('Login')
                            ->maxLength(50)
                            ->unique(User::class, 'login', ignoreRecord: true)
                            ->nullable(),
                        TextInput::make('cpf')
                            ->label('CPF')
                            ->maxLength(14)
                            ->mask('999.999.999-99')
                            ->unique(User::class, 'cpf', ignoreRecord: true)
                            ->nullable(),
                        TextInput::make('phone')
                            ->label('Telefone')
                            ->maxLength(20)
                            ->nullable(),
                    ])->columns(2),

                Section::make('Acesso e Segurança')
                    ->schema([
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(100)
                            ->helperText('Deixe em branco para manter a senha atual (ao editar)'),
                        Select::make('roles')
                            ->label('Perfil de acesso')
                            ->relationship('roles', 'name')
                            ->options(Role::pluck('name', 'id'))
                            ->multiple()
                            ->required()
                            ->preload(),
                        Select::make('client_id')
                            ->label('Cliente vinculado')
                            ->options(Client::orderBy('company_name')->pluck('company_name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->helperText('Obrigatório apenas para usuários com perfil "comum"'),
                        Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                        Toggle::make('must_change_password')
                            ->label('Forçar troca de senha no próximo acesso')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Perfil')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'campo' => 'warning',
                        'comum' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('client.company_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
