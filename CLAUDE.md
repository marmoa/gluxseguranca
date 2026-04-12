# G-Lux - Sistema de Gestão de Serviços de Calibração/Ensaios

## Sobre o Projeto

Reescrita completa de um sistema legado PHP procedural (6 anos em produção) para Laravel 12+ / Filament 4.x / MySQL 8+.

**Domínio:** Gestão de serviços de calibração e ensaios de equipamentos (capacetes, luvas, botas, etc). O fluxo principal é: Cliente → Orçamento → Ordem de Serviço → Inspeção de Campo → Rastreabilidade → Faturamento.

## Stack

- **Backend:** Laravel 12+ / PHP 8.2+
- **Admin UI:** Filament 4.x (3 painéis: Admin `/admin`, Campo `/campo`, Comum `/comum`)
- **Frontend:** Filament usa Tailwind CSS internamente. Para páginas customizadas fora do Filament, usar Bootstrap 5.x (NÃO usar Materialize)
- **Banco:** MySQL 8+
- **Pacotes principais:** spatie/laravel-permission, barryvdh/laravel-dompdf, spatie/laravel-activitylog, maatwebsite/excel, laravel/mcp, laravel/boost (dev)

## Convenções

### Nomenclatura
- Tabelas e campos em **inglês** (convenção Laravel)
- Enums PHP 8.1+ em `app/Enums/`
- Services em `app/Services/`
- Filament Resources organizados por painel: `app/Filament/{Admin,Campo,Comum}/Resources/`
- Filament Pages: `app/Filament/{Admin,Campo,Comum}/Pages/`

### Banco de Dados
- Usar `SoftDeletes` em todas as tabelas de negócio
- Timestamps padrão Laravel (`created_at`, `updated_at`)
- Foreign keys com `constrained()->cascadeOnDelete()` ou `nullOnDelete()` conforme o caso
- Enums como colunas: usar cast para PHP Enum no Model

### Models
- Sempre definir `$fillable` (nunca `$guarded = []`)
- Definir `$casts` para datas, enums e booleanos
- Relacionamentos tipados com return type
- Scopes para filtros recorrentes (ex: `scopeActive`, `scopeForClient`)

### Filament
- Forms: usar `Section`, `Tabs`, `Grid` para organizar
- Tables: sempre incluir `searchable()`, `sortable()` nas colunas relevantes
- Selects dependentes: usar `->reactive()` com `->options(fn (Get $get) => ...)`
- Actions: usar `Action::make()` para operações customizadas (gerar PDF, mudar status, etc)

### Lógica de Negócio Crítica
- **Rastreabilidade:** Faixas configuráveis via `traceability_settings`. Service: `App\Services\TraceabilityCodeService`
- **Inspeções:** Modelo unificado `inspections` + `inspection_values`. 1 registro por unidade individual.
- **Preenchimento em lote:** Operador preenche 1x, sistema replica Nx em `inspections`/`inspection_values`

## Migração de Dados

**IMPORTANTE:** Somente dados mestres serão migrados do sistema legado:
- Usuários, Clientes, Padrões, Itens, Atributos, Valores de Atributos, Normas, Etiquetas, Equipamentos, Estados, Cidades
- **NÃO migrar:** Ordens de Serviço, Rastreamentos, Dados de Serviço, Faturas, Logs
- O sistema legado permanece em produção como consulta read-only do histórico

## Referência do Sistema Legado

O código legado está no mesmo repositório para consulta. Arquivos-chave:
- `pages/geral/functions.php` — Toda lógica de negócio
- `pages/campo/preenchendoItens.php` — Formulário dinâmico de atributos
- `pages/geral/func-inserir.php` — Operações INSERT
- `pages/geral/func-carregaTabelas.php` — Queries de listagem

## Regra Obrigatória: Manter Histórico e Pendências

**A cada implementação concluída, OBRIGATORIAMENTE:**
1. Atualizar `.claude/commands/historico.md` — registrar o que foi feito, com data
2. Atualizar `.claude/commands/pendencias.md` — marcar como concluído e remover da lista

**A cada nova pendência identificada:**
1. Adicionar em `.claude/commands/pendencias.md`

Esses arquivos servem como guia de contexto para todas as sessões de desenvolvimento. Sempre consulte-os antes de iniciar qualquer trabalho para entender o estado atual do projeto.

## Documentação

- `docs/requirements.md` — Levantamento de requisitos
- `docs/architecture.md` — Arquitetura do sistema
- `docs/development-guide.md` — Guia de desenvolvimento
- `.claude/commands/historico.md` — Histórico de tudo que foi implementado (guia de contexto)
- `.claude/commands/pendencias.md` — Lista de pendências não implementadas (guia de contexto)
