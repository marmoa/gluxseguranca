# G-Lux — Guia de Desenvolvimento

## 1. Setup Inicial

### 1.1 Pré-requisitos
- PHP 8.2+
- Composer 2.x
- MySQL 8+
- Node.js 18+ (para assets)
- Laragon (ambiente local)

### 1.2 Criar Projeto

```bash
# Criar projeto Laravel em diretório separado do legado
cd C:\laragon\www
composer create-project laravel/laravel glux

cd glux

# Instalar Filament 4.x
composer require filament/filament:"^4.0"

# Instalar pacotes essenciais
composer require spatie/laravel-permission
composer require barryvdh/laravel-dompdf
composer require spatie/laravel-activitylog
composer require filament/spatie-laravel-settings-plugin
composer require maatwebsite/excel
composer require lucascudo/laravel-pt-br-localization

# Publicar configs
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
```

### 1.3 Configurar `.env`

```env
APP_NAME="G-Lux"
APP_URL=http://glux.test
APP_LOCALE=pt_BR
APP_TIMEZONE=America/Sao_Paulo

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=glux
DB_USERNAME=root
DB_PASSWORD=

# Conexão legada para migração de dados
DB_LEGACY_CONNECTION=mysql
DB_LEGACY_HOST=127.0.0.1
DB_LEGACY_DATABASE=glux_hom
DB_LEGACY_USERNAME=root
DB_LEGACY_PASSWORD=
```

### 1.4 Configurar `config/database.php`

```php
// Adicionar conexão legada
'legacy' => [
    'driver' => 'mysql',
    'host' => env('DB_LEGACY_HOST', '127.0.0.1'),
    'database' => env('DB_LEGACY_DATABASE', 'glux_hom'),
    'username' => env('DB_LEGACY_USERNAME', 'root'),
    'password' => env('DB_LEGACY_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],
```

---

## 2. Multi-Panel Filament

### 2.1 Criar os 3 painéis

```bash
php artisan make:filament-panel admin
php artisan make:filament-panel campo
php artisan make:filament-panel comum
```

### 2.2 Configurar Panel Providers

Cada provider em `app/Providers/Filament/`:

```php
// AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->login()
        ->colors(['primary' => Color::Blue])
        ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
        ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
        ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
        ->middleware([/* ... */])
        ->authMiddleware([Authenticate::class]);
}
```

### 2.3 Middleware de Role

Criar middleware para verificar role por painel:

```php
// app/Http/Middleware/EnsureUserHasRole.php
public function handle($request, Closure $next, string $role)
{
    if (!auth()->user()->hasRole($role)) {
        abort(403);
    }
    return $next($request);
}
```

---

## 3. Padrões de Código

### 3.1 Model Pattern

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'standard_id',
        'state_id',
        'city_id',
        'responsible_user_id',
        'client_contract_id',
        'quote_id',
        'status',
        'open_date',
        'start_date',
        'completion_date',
        'observations',
    ];

    protected $casts = [
        'status' => ServiceOrderStatus::class,
        'open_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', ServiceOrderStatus::Open);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('responsible_user_id', $userId);
    }
}
```

### 3.2 Enum Pattern

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceOrderStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Billed = 'billed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Aberta',
            self::InProgress => 'Em Execução',
            self::Completed => 'Concluída',
            self::Billed => 'Faturada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Billed => 'primary',
            self::Cancelled => 'danger',
        };
    }
}
```

### 3.3 Service Pattern

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RangeExhaustedException;
use App\Models\Inspection;
use App\Models\TraceabilitySetting;
use Illuminate\Support\Facades\DB;

class TraceabilityCodeService
{
    public function generateNextCode(int $digitCount): int
    {
        return DB::transaction(function () use ($digitCount) {
            $setting = TraceabilitySetting::where('digit_count', $digitCount)
                ->where('is_active', true)
                ->lockForUpdate()
                ->firstOrFail();

            $lastCode = Inspection::where('traceability_code', '>=', $setting->range_start)
                ->where('traceability_code', '<=', $setting->range_end)
                ->max('traceability_code');

            $nextCode = $lastCode ? $lastCode + 1 : $setting->range_start;

            if ($nextCode > $setting->range_end) {
                throw new RangeExhaustedException(
                    "Faixa de rastreamento esgotada para {$digitCount} dígitos"
                );
            }

            return $nextCode;
        });
    }

    public function generateBatch(int $digitCount, int $quantity): array
    {
        $startCode = $this->generateNextCode($digitCount);
        return range($startCode, $startCode + $quantity - 1);
    }
}
```

### 3.4 Filament Resource Pattern

```php
<?php

namespace App\Filament\Admin\Resources;

use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Tabs')->tabs([
                Forms\Components\Tabs\Tab::make('Dados Gerais')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('legal_name')
                            ->label('Razão Social')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('trade_name')
                            ->label('Nome Fantasia')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->required()
                            ->unique(ignoreRecord: true),
                    ]),
                ]),
                Forms\Components\Tabs\Tab::make('Endereço')->schema([
                    Forms\Components\Select::make('state_id')
                        ->label('Estado')
                        ->relationship('state', 'name')
                        ->searchable()
                        ->reactive()
                        ->required(),
                    Forms\Components\Select::make('city_id')
                        ->label('Cidade')
                        ->options(fn (Forms\Get $get) =>
                            \App\Models\City::where('state_id', $get('state_id'))
                                ->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Razão Social')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state.abbreviation')
                    ->label('UF')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('state_id')
                    ->label('Estado')
                    ->relationship('state', 'name'),
            ])
            ->defaultSort('legal_name');
    }
}
```

### 3.5 Migration Pattern

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence_number');
            $table->string('result')->default('pending'); // Cast to InspectionResult enum
            $table->string('traceability_code', 10)->nullable()->index();
            $table->string('rejection_code', 10)->nullable();
            $table->string('rejection_category')->nullable(); // Cast to RejectionCategory enum
            $table->text('rejection_description')->nullable();
            $table->string('previous_tracking_code', 10)->nullable();
            $table->unsignedInteger('os_item_number');
            $table->date('retest_date')->nullable()->index();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamp('labeled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['service_order_item_id', 'result']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
```

---

## 4. Guia por Fase

### Fase 1 — Fundação

**Objetivo:** Projeto rodando com auth e dados base.

1. Criar projeto Laravel + instalar pacotes
2. Configurar 3 Filament panels
3. Criar migration `users` customizada (com `must_change_password`, `client_id`)
4. Criar RoleSeeder (admin, campo, comum)
5. Criar migrations: states, cities
6. Criar StateSeeder e CitySeeder (importar do legado)
7. Criar migrations: standards, tags, norms
8. Criar Resources básicos: StandardResource, TagResource, NormResource
9. Testar: login, navegação entre painéis, CRUD básico

### Fase 2 — Dados Mestres

**Objetivo:** Todos os cadastros administrativos funcionando.

1. Migrations: attributes, attribute_values, items, pivots
2. Migrations: standard_attributes, standard_attribute_values
3. Models com todos os relacionamentos
4. AttributeResource com Repeater inline para valores
5. ItemResource com abas (dados, atributos via template, normas)
6. Migrations: clients, client_contracts
7. ClientResource com RelationManager de contratos
8. Migrations: equipment
9. EquipmentResource
10. Testar: criar padrão com template → criar item (atributos pré-selecionados)

### Fase 3 — Orçamentos

**Objetivo:** Tabelas de preço e orçamentos funcionando.

1. Migrations: price_tables, price_table_items, quotes, quote_items
2. Models e relacionamentos
3. PriceTableResource com RelationManager de items/preços
4. QuoteResource com cálculo automático
5. Testar: criar tabela de preço → criar orçamento → aprovar

### Fase 4 — Ordens de Serviço

**Objetivo:** Criar e listar OS nos 3 painéis.

1. Migrations: service_order_numbers, service_orders, quote_numbers
2. ServiceOrderNumberService
3. ServiceOrderResource (admin) com formulário completo e selects reativos
4. ServiceOrderResource (campo) read-only com ações
5. ServiceOrderResource (comum) filtrado por client_id
6. ServiceOrderLifecycleService
7. Testar: criar OS com orçamento vinculado, listar por status nos 3 painéis

### Fase 5 — Operações de Campo

**Objetivo:** O core do sistema — preenchimento de inspeções.

1. Migrations: service_order_items, inspections, inspection_values, traceability_settings
2. TraceabilityCodeService
3. InspectionService (criar batch, aprovar, reprovar, editar)
4. TraceabilitySettingSeeder
5. Pages campo: StartService, AddItems
6. **FillItemData** (page mais complexa):
   - Livewire page com formulário dinâmico
   - Carregar atributos do item
   - Suporte lote (replicar) e individual
   - Gerar códigos de rastreamento
7. Pages: ServiceSummary, RejectedItems, BatchEdit
8. RecordTemperatureHumidity
9. Testar workflow completo: iniciar → preencher → aprovar/reprovar → fechar

### Fase 6-10 — Ver plano detalhado

---

## 5. Migração de Dados do Legado

### Comando principal

```bash
php artisan migrate:legacy-data
```

### Escopo: SOMENTE DADOS MESTRES

**Migrar:**
1. states (estado)
2. cities (cidade)
3. users (usuarios) — preservar MD5, mapear role via Spatie
4. standards (padroes) + standard_attributes
5. tags (etiquetas)
6. norms (normas)
7. attributes (atributos)
8. attribute_values (atributo_valor)
9. items (itens) + pivots (item_attribute, item_attribute_value, item_norm)
10. clients (clientes)
11. client_contracts (cliente_contrato)
12. equipment (equipamentos)

**NÃO migrar** (sistema legado fica como consulta read-only):
- ordem_servico, numero_os, numero_orcamento
- os_itens_quantidade, dados_servico
- rastreamento, rastreamento_reprovado
- faturas, log

### Transformações

```php
// Usuários: preservar MD5, LegacyMd5Hasher faz rehash no login
// Roles: permissao 'admin'|'campo'|'comum' → Spatie role
// Status: ativo='S' → active, ativo='N' → soft deleted
```

---

## 6. Testes

### Executar

```bash
# Todos os testes
php artisan test

# Específico
php artisan test --filter=TraceabilityCodeServiceTest

# Com coverage
php artisan test --coverage
```

### Prioridade de testes

1. `TraceabilityCodeServiceTest` — 100% coverage obrigatório
2. `InspectionServiceTest` — workflow completo
3. `ServiceOrderLifecycleTest` — transições de status
4. Feature tests dos Resources Filament
