# G-Lux — Arquitetura do Sistema

## 1. Visão de Alto Nível

```
┌─────────────────────────────────────────────────────────┐
│                      FRONTEND                            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐              │
│  │  Admin   │  │  Campo   │  │  Comum   │  Filament 4  │
│  │  Panel   │  │  Panel   │  │  Panel   │  (Livewire)  │
│  │ /admin   │  │ /campo   │  │ /comum   │              │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘              │
│       │              │              │                    │
│  ┌────┴──────────────┴──────────────┴─────┐             │
│  │          Unified Login (/)              │             │
│  └────────────────┬───────────────────────┘             │
└───────────────────┼─────────────────────────────────────┘
                    │
┌───────────────────┼─────────────────────────────────────┐
│                   │        BACKEND                       │
│  ┌────────────────┴───────────────────────┐             │
│  │           Laravel 12+ / PHP 8.2+       │             │
│  │                                         │             │
│  │  ┌─────────┐ ┌──────────┐ ┌─────────┐ │             │
│  │  │ Models  │ │ Services │ │ Enums   │ │             │
│  │  └────┬────┘ └────┬─────┘ └─────────┘ │             │
│  │       │           │                     │             │
│  │  ┌────┴───────────┴─────────────────┐  │             │
│  │  │        Eloquent ORM              │  │             │
│  │  └──────────────┬───────────────────┘  │             │
│  └─────────────────┼──────────────────────┘             │
│                    │                                     │
│  ┌─────────────────┴──────────────────────┐             │
│  │            MySQL 8+                     │             │
│  └─────────────────────────────────────────┘             │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │  Laravel     │  │  DomPDF      │  │  Spatie      │  │
│  │  Mail/Queue  │  │  Reports     │  │  ActivityLog │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└──────────────────────────────────────────────────────────┘
                    │
┌───────────────────┼─────────────────────────────────────┐
│              INTEGRAÇÕES EXTERNAS                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │  WhatsApp    │  │  Telegram    │  │  Claude/     │  │
│  │  Business    │  │  Bot API     │  │  OpenAI API  │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
│                                                          │
│  ┌───────────────────────────────────────────────────┐  │
│  │  MCP Server (laravel-boost/mcp)                   │  │
│  │  Expõe tools e resources para agentes de IA       │  │
│  │  Tools: search_certificate, list_invoices, etc.   │  │
│  │  Resources: clients, items, inspections           │  │
│  └───────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
```

## 2. Estrutura de Diretórios

```
glux/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── MigrateLegacyData.php        # Migração do banco legado
│   │       ├── CheckExpiringInspections.php  # Job diário de alertas
│   │       └── CheckOverdueInvoices.php      # Job diário de faturas vencidas
│   │
│   ├── Enums/
│   │   ├── ServiceOrderStatus.php    # open, in_progress, completed, billed, cancelled
│   │   ├── InspectionResult.php      # pending, approved, rejected
│   │   ├── AttributeInputType.php    # text, select
│   │   ├── QuoteStatus.php           # draft, sent, approved, rejected, expired
│   │   ├── InvoiceStatus.php         # pending, sent, overdue, paid, cancelled
│   │   └── RejectionCategory.php     # visual, electrical, dimensional
│   │
│   ├── Exceptions/
│   │   ├── RangeExhaustedException.php
│   │   └── InvalidStatusTransitionException.php
│   │
│   ├── Filament/
│   │   ├── Admin/
│   │   │   ├── Resources/
│   │   │   │   ├── ClientResource.php
│   │   │   │   ├── ClientResource/
│   │   │   │   │   ├── Pages/
│   │   │   │   │   └── RelationManagers/
│   │   │   │   │       └── ContractsRelationManager.php
│   │   │   │   ├── ServiceOrderResource.php
│   │   │   │   ├── ItemResource.php
│   │   │   │   ├── ItemResource/
│   │   │   │   │   └── RelationManagers/
│   │   │   │   │       ├── AttributesRelationManager.php
│   │   │   │   │       └── NormsRelationManager.php
│   │   │   │   ├── StandardResource.php
│   │   │   │   ├── AttributeResource.php
│   │   │   │   ├── NormResource.php
│   │   │   │   ├── TagResource.php
│   │   │   │   ├── EquipmentResource.php
│   │   │   │   ├── UserResource.php
│   │   │   │   ├── InvoiceResource.php
│   │   │   │   ├── ContractResource.php
│   │   │   │   ├── PriceTableResource.php
│   │   │   │   ├── QuoteResource.php
│   │   │   │   ├── TagInventoryResource.php
│   │   │   │   └── TagDistributionResource.php
│   │   │   │
│   │   │   ├── Pages/
│   │   │   │   ├── Dashboard.php
│   │   │   │   ├── CalibrationControl.php
│   │   │   │   ├── SystemLog.php
│   │   │   │   ├── TraceabilitySettings.php
│   │   │   │   ├── Reports.php
│   │   │   │   └── ExpiringItems.php
│   │   │   │
│   │   │   └── Widgets/
│   │   │       ├── ServiceOrderStatsOverview.php
│   │   │       ├── UpcomingServicesWidget.php
│   │   │       ├── InvoiceStatsWidget.php
│   │   │       ├── ExpiringInspectionsWidget.php
│   │   │       └── TagStockWidget.php
│   │   │
│   │   ├── Campo/
│   │   │   ├── Resources/
│   │   │   │   └── ServiceOrderResource.php   # Read-only variant
│   │   │   └── Pages/
│   │   │       ├── Dashboard.php
│   │   │       ├── StartService.php
│   │   │       ├── AddItems.php
│   │   │       ├── FillItemData.php            # Mais complexa
│   │   │       ├── RecordTemperatureHumidity.php
│   │   │       ├── ServiceSummary.php
│   │   │       ├── RejectedItems.php
│   │   │       └── BatchEdit.php
│   │   │
│   │   └── Comum/
│   │       ├── Resources/
│   │       │   ├── ServiceOrderResource.php    # Filtrado por client_id
│   │       │   └── InvoiceResource.php         # Read-only, filtrado
│   │       └── Pages/
│   │           ├── Dashboard.php
│   │           ├── CertificateSearch.php
│   │           └── VehicleSearch.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ChatbotWebhookController.php
│   │   └── Requests/
│   │       └── (Form Requests por Resource)
│   │
│   ├── Mail/
│   │   ├── ServiceOrderCompleted.php
│   │   ├── InvoiceCreated.php
│   │   ├── InvoiceOverdue.php
│   │   ├── ExpiringItemsAlert.php
│   │   └── QuoteSent.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── ClientContract.php
│   │   ├── Standard.php
│   │   ├── Item.php
│   │   ├── Attribute.php
│   │   ├── AttributeValue.php
│   │   ├── Norm.php
│   │   ├── Tag.php
│   │   ├── Equipment.php
│   │   ├── State.php
│   │   ├── City.php
│   │   ├── ServiceOrder.php
│   │   ├── ServiceOrderItem.php
│   │   ├── ServiceOrderNumber.php
│   │   ├── Inspection.php
│   │   ├── InspectionValue.php
│   │   ├── TraceabilitySetting.php
│   │   ├── Invoice.php
│   │   ├── PriceTable.php
│   │   ├── PriceTableItem.php
│   │   ├── Quote.php
│   │   ├── QuoteItem.php
│   │   ├── QuoteNumber.php
│   │   ├── NotificationSetting.php
│   │   ├── TagInventory.php
│   │   ├── TagDistribution.php
│   │   ├── TagConsumption.php
│   │   ├── ChatbotConversation.php
│   │   └── ChatbotMessage.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── Filament/
│   │       ├── AdminPanelProvider.php
│   │       ├── CampoPanelProvider.php
│   │       └── ComumPanelProvider.php
│   │
│   ├── Reports/                         # Classes de relatório plugáveis
│   │   ├── ServiceSummaryReport.php
│   │   ├── TraceabilityReport.php
│   │   ├── PerItemReport.php
│   │   ├── RejectionReport.php
│   │   └── BaseReport.php
│   │
│   └── Services/
│       ├── TraceabilityCodeService.php
│       ├── ServiceOrderNumberService.php
│       ├── ServiceOrderLifecycleService.php
│       ├── InspectionService.php
│       ├── ReportService.php
│       └── ChatbotService.php
│
├── database/
│   ├── migrations/                      # ~30 migrations em ordem de dependência
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── StateSeeder.php
│   │   ├── CitySeeder.php
│   │   ├── RoleSeeder.php
│   │   └── TraceabilitySettingSeeder.php
│   └── factories/                       # Para testes
│
├── resources/
│   └── views/
│       ├── reports/
│       │   ├── partials/
│       │   │   └── header-footer.blade.php
│       │   ├── service-summary.blade.php
│       │   ├── traceability-report.blade.php
│       │   ├── traceability-landscape.blade.php
│       │   ├── per-item-report.blade.php
│       │   ├── per-item-landscape.blade.php
│       │   ├── rejection-report.blade.php
│       │   └── rejection-landscape.blade.php
│       ├── emails/
│       │   ├── service-order-completed.blade.php
│       │   ├── invoice-created.blade.php
│       │   ├── invoice-overdue.blade.php
│       │   └── expiring-items.blade.php
│       └── filament/
│           └── campo/pages/
│               └── fill-item-data.blade.php
│
├── tests/
│   ├── Feature/
│   │   ├── Admin/
│   │   ├── Campo/
│   │   ├── Comum/
│   │   └── Auth/
│   └── Unit/
│       ├── Services/
│       │   ├── TraceabilityCodeServiceTest.php
│       │   ├── InspectionServiceTest.php
│       │   └── ServiceOrderLifecycleTest.php
│       └── Models/
│
├── docs/
│   ├── requirements.md
│   ├── architecture.md
│   └── development-guide.md
│
├── .claude/
│   └── commands/                        # Skills do Claude Code
│       ├── create-migration.md
│       ├── create-model.md
│       ├── create-filament-resource.md
│       ├── create-filament-page.md
│       ├── create-service.md
│       └── check-legacy.md
│
├── .github/
│   └── copilot-instructions.md          # Instruções GitHub Copilot
│
└── CLAUDE.md                            # Instruções Claude Code
```

## 3. Modelo de Dados (ERD Simplificado)

### Domínio Principal: Serviços

```
┌──────────┐     ┌───────────────┐     ┌────────────────────┐
│ clients  │────<│ service_orders│────<│ service_order_items │
└──────────┘     └───────────────┘     └────────────────────┘
     │                  │                        │
     │                  │                  ┌─────┴──────┐
     │                  │                  │ inspections │
     │                  │                  └─────┬──────┘
     │                  │                        │
     │                  │               ┌────────┴─────────┐
     │                  │               │inspection_values  │
     │                  │               └──────────────────┘
     │                  │
     │           ┌──────┴──────┐
     │           │  invoices   │
     │           └─────────────┘
     │
     │     ┌──────────────┐     ┌──────────────────┐
     └────<│ quotes       │────<│ quote_items       │
           └──────────────┘     └──────────────────┘
                  │
           ┌──────┴──────────┐     ┌────────────────────┐
           │ price_tables    │────<│ price_table_items   │
           └─────────────────┘     └────────────────────┘
```

### Domínio: Catálogo de Itens

```
┌───────────┐     ┌─────────┐     ┌──────────────────────┐
│ standards │────<│  items  │────<│ item_attribute (pivot)│
└───────────┘     └─────────┘     └──────────┬───────────┘
      │                │                      │
      │                │              ┌───────┴──────┐
      │                │              │  attributes  │
      │                │              └───────┬──────┘
      │                │                      │
      │           ┌────┴──────────────────────┴──────────┐
      │           │    item_attribute_value (pivot)       │
      │           └──────────────────┬───────────────────┘
      │                              │
      │                    ┌─────────┴────────┐
      │                    │ attribute_values  │
      │                    └──────────────────┘
      │
      ├────<┌──────────────────────┐
      │     │ standard_attributes  │
      │     └──────────┬───────────┘
      │                │
      │     ┌──────────┴───────────────┐
      │     │standard_attribute_values  │
      │     └──────────────────────────┘
```

### Domínio: Etiquetas

```
┌──────┐     ┌───────────────┐     ┌────────────────────┐
│ tags │────<│ tag_inventory │────<│ tag_distributions  │
└──────┘     └───────────────┘     └────────┬───────────┘
                                             │
                                    ┌────────┴───────────┐
                                    │ tag_consumptions   │
                                    └────────────────────┘
```

## 4. Fluxos de Dados Principais

### Fluxo 1: Ciclo de Vida da OS

```
Orçamento Aprovado
       ↓
┌──────────────┐   Admin cria    ┌──────────────┐
│  OS: OPEN    │ ──────────────→ │ OS: IN_PROG  │
└──────────────┘   Campo inicia  └──────┬───────┘
                                        │
                    Campo preenche      │
                    inspeções           ↓
                                ┌──────────────┐
                    Campo fecha │ OS: COMPLETED│
                    a OS   ←── └──────┬───────┘
                                      │
                    Admin fatura      │
                                      ↓
                                ┌──────────────┐
                                │ OS: BILLED   │
                                └──────────────┘
```

### Fluxo 2: Preenchimento de Inspeção (Campo)

```
Técnico seleciona item + quantidade (N)
       ↓
Sistema cria:
  1x service_order_item (lote)
  Nx inspections (status: pending)
       ↓
Técnico preenche atributos (1x)
       ↓
Sistema replica para N inspections:
  - UPDATE result (approved/rejected)
  - Gera traceability_code (se approved)
  - INSERT Nx inspection_values por atributo
       ↓
Total: N inspections + N*M inspection_values
(M = número de atributos do item)
```

### Fluxo 3: Alertas de Validade

```
Job diário (CheckExpiringInspections)
       ↓
SELECT inspections WHERE retest_date BETWEEN hoje AND hoje+30
       ↓
Agrupa por cliente
       ↓
├── Email consolidado por cliente
└── Filament Notification in-app
```

## 5. Segurança

### Autenticação
- Laravel Auth com bcrypt
- Migração MD5 → bcrypt via LegacyMd5Hasher (temporário)
- Session timeout configurável
- Rate limiting em login

### Autorização
- spatie/laravel-permission com 3 roles: admin, campo, comum
- Filament Panels com middleware de role
- Policies por Model para controle granular
- Usuários "comum" filtrados por client_id

### Dados
- Prepared statements via Eloquent (sem SQL injection)
- CSRF em todos os formulários (Livewire/Filament automático)
- Validação via Form Requests
- SoftDeletes (dados nunca são perdidos)

## 6. Pacotes e Dependências

| Pacote | Versão | Uso |
|--------|--------|-----|
| laravel/framework | 12.x | Core |
| filament/filament | 4.x | UI Panels |
| spatie/laravel-permission | 6.x | Roles/Permissions |
| barryvdh/laravel-dompdf | 3.x | Geração de PDFs |
| spatie/laravel-activitylog | 4.x | Audit logging |
| filament/spatie-laravel-settings-plugin | * | Settings UI |
| lucascudo/laravel-pt-br-localization | * | PT-BR validation |
| maatwebsite/excel | 3.x | Export Excel |
| laravel-boost/mcp | * | Servidor MCP (Model Context Protocol) |
| twilio/sdk | * | WhatsApp (Fase 10) |
| irazasyed/telegram-bot-sdk | * | Telegram (Fase 10) |
