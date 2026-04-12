# G-Lux — Instruções para GitHub Copilot

## Contexto do Projeto

Este é o projeto G-Lux, um sistema de gestão de serviços de calibração/ensaios de equipamentos. Está sendo reescrito de PHP procedural para **Laravel 12+ / Filament 4.x / MySQL 8+**. O sistema legado permanece em produção como consulta read-only do histórico — somente dados mestres são migrados (usuários, clientes, padrões, itens, atributos). NÃO usar Materialize CSS — usar Bootstrap 5 para páginas fora do Filament.

## Stack

- Laravel 12+ / PHP 8.2+
- Filament 4.x (3 painéis: Admin, Campo, Comum)
- MySQL 8+
- spatie/laravel-permission para roles
- barryvdh/laravel-dompdf para PDFs
- spatie/laravel-activitylog para logs
- laravel-boost/mcp para servidor MCP (Model Context Protocol) — expõe tools e resources para agentes de IA

## Convenções de Código

### PHP / Laravel
- PHP 8.2+ features: enums, readonly properties, named arguments, match expressions
- Strict types em todos os arquivos: `declare(strict_types=1);`
- Tabelas e campos em **inglês** (convenção Laravel)
- Models com `$fillable` (nunca `$guarded = []`), `$casts`, `SoftDeletes`
- Services em `app/Services/` para lógica de negócio
- Enums em `app/Enums/` (backed enums)
- Form Requests para validação
- DB::transaction() para operações multi-tabela

### Filament
- Resources por painel: `app/Filament/{Admin,Campo,Comum}/Resources/`
- Pages customizadas: `app/Filament/{Admin,Campo,Comum}/Pages/`
- Forms com `Section`, `Tabs`, `Grid`
- Selects dependentes com `->reactive()` e `->options(fn (Get $get) => ...)`
- Labels em **português do Brasil**
- Datas: `->date('d/m/Y')`
- Moeda: `->money('BRL')`

### Banco de Dados
- SoftDeletes em tabelas de negócio
- Foreign keys com `->constrained()`
- Índices em campos de busca e foreign keys
- Enums como string columns com cast no Model

## Estrutura do Projeto

```
app/
├── Enums/                    # PHP Enums (ServiceOrderStatus, AttributeInputType, etc.)
├── Exceptions/               # Exceções customizadas
├── Filament/
│   ├── Admin/Resources/      # Resources do painel administrativo
│   ├── Admin/Pages/          # Pages customizadas admin
│   ├── Admin/Widgets/        # Widgets do dashboard admin
│   ├── Campo/Resources/      # Resources do painel de campo
│   ├── Campo/Pages/          # Pages customizadas campo (FillItemData, etc.)
│   └── Comum/Resources/      # Resources do portal do cliente
├── Models/                   # Eloquent Models
├── Services/                 # Lógica de negócio
│   ├── TraceabilityCodeService.php
│   ├── ServiceOrderNumberService.php
│   ├── InspectionService.php
│   ├── ReportService.php
│   └── ServiceOrderLifecycleService.php
├── Mail/                     # Mailables
└── Providers/
    └── Filament/             # Panel Providers (Admin, Campo, Comum)
```

## Modelo de Dados Principal

```
Client → ServiceOrder → ServiceOrderItem → Inspection → InspectionValue
                ↑                                ↑
              Quote                         Attribute
                ↑
           PriceTable
```

- **Inspection**: 1 registro por unidade individual (não por lote)
- **InspectionValue**: 1 registro por atributo medido por unidade
- **Preenchimento em lote**: operador preenche 1x, sistema replica N inspections + N*M inspection_values

## Regras de Negócio Importantes

1. **Rastreabilidade**: Códigos sequenciais configuráveis via `traceability_settings` (range_start/range_end por digit_count)
2. **Inspeções**: result é enum (pending/approved/rejected), não atributo. traceability_code na inspection.
3. **OS Lifecycle**: open → in_progress → completed → billed
4. **Template de atributos**: Standards têm atributos-template (standard_attributes). Itens herdam ao ser criados.

## Regra Obrigatória: Manter Histórico e Pendências

**A cada implementação concluída, OBRIGATORIAMENTE:**
1. Atualizar `.claude/commands/historico.md` — registrar o que foi feito, com data
2. Atualizar `.claude/commands/pendencias.md` — marcar como concluído e remover da lista

**A cada nova pendência identificada:**
1. Adicionar em `.claude/commands/pendencias.md`

Esses arquivos servem como guia de contexto. **Sempre consulte-os antes de iniciar qualquer trabalho** para entender o estado atual do projeto.

## Arquivos de Referência (Skills)

Para entender o projeto e seguir as convenções, consulte estes arquivos em `.claude/commands/`:

| Arquivo | Uso |
|---------|-----|
| `historico.md` | O que já foi implementado — consultar ANTES de trabalhar |
| `pendencias.md` | O que falta implementar — consultar ANTES de trabalhar |
| `create-migration.md` | Convenções para criar migrations |
| `create-model.md` | Convenções para criar Models Eloquent |
| `create-filament-resource.md` | Convenções para criar Filament Resources |
| `create-filament-page.md` | Convenções para criar Filament Pages customizadas |
| `create-service.md` | Convenções para criar Service classes |
| `check-legacy.md` | Guia para consultar o código do sistema legado |

Documentação adicional:
- `docs/requirements.md` — Requisitos funcionais e não-funcionais
- `docs/architecture.md` — Arquitetura, ERD, fluxos de dados
- `docs/development-guide.md` — Setup, padrões de código, guia por fase

## Ao sugerir código

- Sempre use type hints e return types
- Prefira Eloquent sobre queries raw
- Use eager loading (`with()`) para evitar N+1
- Bulk insert (`Model::insert()`) para operações em massa
- Filament Actions para operações customizadas
- Não crie helpers genéricos — use Services específicos
