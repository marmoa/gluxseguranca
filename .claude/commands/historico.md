# Histórico de Implementação — G-Lux Segurança

> Este arquivo serve como guia de contexto. Registra tudo que foi implementado no sistema.
> **IMPORTANTE:** Este arquivo DEVE ser atualizado a cada implementação concluída.

---

## Fase 0 — Planejamento (Concluído em 12/04/2026)

### Análise do Sistema Legado
- [x] Análise completa do sistema legado PHP procedural (~200 arquivos)
- [x] Mapeamento de 27 tabelas do banco de dados
- [x] Identificação de 12 módulos funcionais existentes
- [x] Análise detalhada de 3 pontos críticos:
  - Gerenciamento de Itens/Atributos (simplificado de 6 etapas para 2)
  - Registro de Dados Medidos (modelo unificado inspections + inspection_values)
  - Código de Rastreabilidade (faixa configurável, sem lógica par/ímpar)

### Documentação Criada
- [x] `CLAUDE.md` — Instruções gerais do Claude Code
- [x] `.github/copilot-instructions.md` — Instruções GitHub Copilot
- [x] `docs/requirements.md` — 20 requisitos funcionais + 6 não-funcionais
- [x] `docs/architecture.md` — Diagramas, estrutura, ERD, fluxos
- [x] `docs/development-guide.md` — Setup, padrões de código, guia por fase
- [x] `.claude/commands/` — 6 skills criadas (migration, model, resource, page, service, check-legacy)

### Decisões Arquiteturais
- [x] Stack: Laravel 12+ / Filament 4.x / MySQL 8+ / Bootstrap 5 (fora do Filament)
- [x] 3 painéis Filament: Admin, Campo, Comum
- [x] Nomenclatura em inglês (convenção Laravel)
- [x] Template de atributos por Padrão (standard_attributes)
- [x] Modelo unificado: inspections + inspection_values (substitui 3 tabelas)
- [x] Rastreabilidade com faixa configurável (traceability_settings)
- [x] Migração somente de dados mestres (legado fica como consulta read-only)
- [x] NÃO usar Materialize CSS — Bootstrap 5 para páginas fora do Filament

### Novos Módulos Planejados
- [x] RF-15: Orçamentos e Tabela de Preços
- [x] RF-16: Gestão de Faturas Expandida (portal do cliente)
- [x] RF-17: Relatórios Gerenciais (estrutura extensível)
- [x] RF-18: Notificações e Alertas de Validade
- [x] RF-19: Gestão de Etiquetas com Controle de Estoque
- [x] RF-20: Chatbot IA (WhatsApp/Telegram)
- [x] RF-21: Servidor MCP via laravel-boost/mcp (comunicação com agentes de IA)

---

## Fase 1 — Fundação (Concluído em 12/04/2026)

### Scaffold e Instalação
- [x] Projeto Laravel 12.12.2 criado em `C:\laragon\www\gluxseguranca`
- [x] Filament 4.x instalado (`filament/filament:"^4.0"`)
- [x] Pacotes instalados: `spatie/laravel-permission`, `bezhansalleh/filament-shield`, `barryvdh/laravel-dompdf`, `spatie/laravel-activitylog`, `pxlrbt/filament-activity-log`, `maatwebsite/excel`, `lucascudo/laravel-pt-br-localization`
- [x] `.env` configurado: `DB_CONNECTION=mysql`, `DB_DATABASE=gluxseguranca`, `APP_LOCALE=pt_BR`, `APP_TIMEZONE=America/Bahia`, legacy DB connection

### Filament Panels
- [x] `AdminPanelProvider` — painel `/admin`, cor Midnight Blue `#2c3e50`, discover em `Filament/Admin/`
- [x] `CampoPanelProvider` — painel `/campo`, cor Midnight Blue
- [x] `ComumPanelProvider` — painel `/comum`, cor Midnight Blue
- [x] Estrutura de diretórios criada: `app/Filament/{Admin,Campo,Comum}/{Resources,Pages,Widgets}/`
- [x] `app/Services/` e `app/Enums/` criados

### Autenticação e Segurança
- [x] `LegacyMd5Hasher` criado em `app/Auth/` — migração transparente MD5→bcrypt
- [x] `AppServiceProvider` configurado para usar `LegacyMd5Hasher`
- [x] `User` model atualizado: campos extras (login, cpf, phone, must_change_password, is_active, client_id), `HasRoles`, `SoftDeletes`, `FilamentUser::canAccessPanel()` por role

### Banco de Dados
- [x] Migration `users` customizada: login, cpf, phone, must_change_password, is_active, client_id, softDeletes
- [x] Migration `states` (estados brasileiros)
- [x] Migration `cities` (cidades vinculadas a estados)
- [x] Migration `standards` (padrões de serviço)
- [x] Migration `tags` (etiquetas)
- [x] Migration `norms` (normas técnicas)
- [x] Migration `clients` (clientes + FK para users.client_id adicionada aqui)
- [x] Conexão `legacy` configurada em `config/database.php` para banco legado `glux_hom`
- [x] `php artisan migrate` executado com sucesso — 13 migrations rodadas

### Models
- [x] `User` (customizado)
- [x] `State`, `City`
- [x] `Standard`, `Tag`, `Norm`
- [x] `Client`

### Seeders
- [x] `RoleSeeder` — cria roles: admin, campo, comum
- [x] `StateSeeder` — insere 27 estados brasileiros
- [x] `DatabaseSeeder` — chama seeders em ordem + cria usuário admin (`admin@glux.com.br` / `admin123`)
- [x] `php artisan db:seed` executado com sucesso

### Recursos Filament (Admin)
- [x] `StandardResource` — CRUD padrões, labels PT-BR, filtros, softDeletes
- [x] `TagResource` — CRUD etiquetas, labels PT-BR, filtros, softDeletes
- [x] `NormResource` — CRUD normas, labels PT-BR, filtros, softDeletes

### Arquivos de Planejamento
- [x] `docs/`, `.claude/commands/`, `CLAUDE.md`, `.github/` copiados para o novo projeto

### Login Unificado e Autorização
- [x] Rota raiz `/` → redireciona para painel correto por role (admin/campo/comum) ou `/admin/login` se não autenticado
- [x] `EnsureRole` middleware criado em `app/Http/Middleware/EnsureRole.php`
- [x] Middleware registrado como alias `role` em `bootstrap/app.php`

### CitySeeder
- [x] `CitySeeder` importa 5.564 cidades do banco legado (`glux_hom`) via conexão `legacy`
- [x] Bulk insert em lotes de 500 para performance
- [x] Integrado ao `DatabaseSeeder`

### Testes de Smoke
- [x] `php artisan migrate` — 13 migrations, sem erros
- [x] `php artisan db:seed` — roles, estados, cidades, usuário admin criados
- [x] Usuário admin@glux.com.br / bcrypt OK (LegacyMd5Hasher funcionando)
- [x] 27 estados, 5.564 cidades, 3 roles, 1 usuário
- [x] Rotas `/`, `/admin`, `/campo`, `/comum` registradas e respondendo

---

## Fase 2 — Dados Mestres (Concluído em 12/04/2026)

### Resources adicionados à Fase 1 (Estados e Cidades)
- [x] `StateResource` — CRUD estados, badge UF, contagem de cidades
- [x] `CityResource` — CRUD cidades, select dependente de estado, filtro por UF

### Migrations
- [x] `attributes` + `attribute_values` (input_type enum: text|select)
- [x] `items` + `item_attribute` (pivot) + `item_attribute_value` (pivot) + `item_norm` (pivot)
- [x] `standard_attributes` + `standard_attribute_values` (template de atributos por padrão)
- [x] `client_contracts`
- [x] `equipment`

### Enums
- [x] `AttributeInputType` (text|select) com método `label()` em PT-BR

### Models
- [x] `Attribute` (HasMany AttributeValue, BelongsToMany Item/Standard)
- [x] `AttributeValue` (BelongsTo Attribute)
- [x] `Item` (BelongsTo Standard/Tag, BelongsToMany Attribute/AttributeValue/Norm)
- [x] `StandardAttribute` (pivot model com defaultValues)
- [x] `Standard` atualizado: BelongsToMany Attribute via standard_attributes, HasMany StandardAttribute
- [x] `ClientContract` (BelongsTo Client, SoftDeletes)
- [x] `Equipment` (SoftDeletes, isCalibrationOverdue(), scopeOverdue)
- [x] `Client` atualizado: HasMany contracts

### Resources Filament (Admin)
- [x] `AttributeResource` — CRUD atributos com Repeater inline de valores (visível só para tipo=select), live()
- [x] `StandardResource` atualizado — aba "Template de Atributos" com CheckboxList BelongsToMany
- [x] `ItemResource` — formulário em 3 abas (Dados Básicos, Atributos, Normas); ao selecionar Padrão, pré-seleciona atributos do template via afterStateUpdated
- [x] `ClientResource` — formulário completo (empresa, endereço com select estado→cidade reativo, responsável, dados adicionais); RelationManager de contratos
- [x] `EquipmentResource` — CRUD equipamentos, destaque vermelho no vencimento de calibração, filtro "calibração vencida"
- [x] `UserResource` — CRUD usuários com atribuição de roles (admin/campo/comum), select de cliente vinculado, toggle de status e troca de senha forçada

### Complementos
- [x] Pacotes `laravel/mcp` (v0.6.5) e `laravel/boost` (v2.4.3) instalados
- [x] Super admin criado: `superadmin@glux.com.br` / `Glux@2026!` (role: admin, must_change_password: false)
- [x] `bezhansalleh/filament-shield` configurado no painel admin via `shield:install admin`
- [x] 132 permissões geradas via `shield:generate --all --panel=admin` para 11 entidades
- [x] Role `super_admin` atribuída ao super admin via `shield:super-admin`
- [x] `User::canAccessPanel()` atualizado para aceitar roles `admin` e `super_admin` no painel admin
- [x] `DatabaseSeeder` atualizado: executa `shield:generate` e `shield:super-admin` automaticamente no seed

---

## Fase 3 — Orçamentos e Preços (Concluído em 12/04/2026)

### Migrations
- [x] `price_tables` (name, description, is_active, softDeletes)
- [x] `price_table_items` (price_table_id, item_id, unit_price, notes; unique por par)
- [x] `quotes` (number, client_id, price_table_id, user_id, status, valid_until, total, notes, rejection_reason, sent_at, approved_at, rejected_at, softDeletes)
- [x] `quote_items` (quote_id, item_id, quantity, unit_price, subtotal como coluna computed `quantity * unit_price`)

### Enum
- [x] `QuoteStatus` (draft/sent/approved/rejected/expired) com métodos `label()` e `color()`

### Models
- [x] `PriceTable` (HasMany items/quotes, scopeActive)
- [x] `PriceTableItem` (BelongsTo PriceTable/Item)
- [x] `Quote` (BelongsTo Client/PriceTable/User, HasMany items, método `recalculateTotal()`, scopes)
- [x] `QuoteItem` (BelongsTo Quote/Item, cast subtotal)

### Resources Filament (Admin)
- [x] `PriceTableResource` — CRUD tabelas de preços + `ItemsRelationManager` (item + preço, pré-preenchimento automático)
- [x] `QuoteResource` — formulário completo, badge de status colorido, filtros por status/cliente; Actions: Enviar / Aprovar / Rejeitar (com motivo); `ItemsRelationManager` com pré-preenchimento de preço pela tabela vinculada e recalculate total automático
- [x] Permissões Shield geradas: 156 permissões para 13 entidades

---

## Fase 4 — Ordens de Serviço (Concluído em 12/04/2026)

### Enum
- [x] `ServiceOrderStatus` (Open/InProgress/Completed/Billed/Cancelled) com métodos `label()`, `color()`, `canTransitionTo()` para validar transições de estado

### Migrations
- [x] `service_order_numbers` (year, last_number, prefix; unique em year)
- [x] `service_orders` (number, FKs: client_id, client_contract_id, quote_id, user_id, state_id, city_id; status, address, temperature, humidity, notes, scheduled_at, started_at, completed_at, billed_at; softDeletes; índices em status, client_id, user_id, scheduled_at)

### Models
- [x] `ServiceOrderNumber` (fillable: year, last_number, prefix; casts: year→int, last_number→int)
- [x] `ServiceOrder` (fillable completo, casts para enum/datas/decimais, 6 BelongsTo relations, 4 scopes, helper `isEditable()`)

### Services
- [x] `ServiceOrderNumberService::generate()` — gera número sequencial `OS-2026-0001` via `DB::transaction` + `lockForUpdate()` (thread-safe)
- [x] `ServiceOrderLifecycleService` — métodos `start()`, `complete()`, `bill()`, `cancel()`, `reopen()` com validação via `canTransitionTo()`, DB::transaction por transição, side-effects de data (started_at, completed_at, billed_at)

### Resources Filament
- [x] **Admin** `ServiceOrderResource` — formulário completo (4 seções: Dados da OS, Local, Agenda, Observações), selects reativos (estado→cidade, cliente→contrato, cliente→orçamento), número gerado automaticamente via service; tabela com 5 actions inline de ciclo de vida (Iniciar/Concluir/Faturar/Reabrir/Cancelar) + Edit/Delete/Restore; navigate a grupo "Operações"
- [x] **Campo** `ServiceOrderResource` — read-only; filtrado por `user_id` do técnico logado; actions: Iniciar (Open→InProgress) e Concluir (InProgress→Completed); infolist de detalhes; sem create/edit/delete
- [x] **Comum** `ServiceOrderResource` — portal do cliente; read-only; filtrado por `client_id` do usuário logado; apenas visualização de OS; infolist de detalhes; sem ações de dados

### Shield
- [x] 168 permissões geradas para 14 entidades (adicionado ServiceOrderResource)

---

## Fase 5 — Operações de Campo
_Ainda não iniciada_

---

## Fase 6 — Etiquetas com Estoque
_Ainda não iniciada_

---

## Fase 7 — Financeiro Expandido
_Ainda não iniciada_

---

## Fase 8 — Relatórios e Alertas
_Ainda não iniciada_

---

## Fase 9 — Polish e Go-Live
_Ainda não iniciada_

---

## Fase 10 — Chatbot IA
_Ainda não iniciada_
