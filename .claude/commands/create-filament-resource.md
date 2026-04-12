Crie um Filament Resource para o projeto G-Lux seguindo as convenções:

## Regras obrigatórias:
1. Resources organizados por painel: `app/Filament/{Admin,Campo,Comum}/Resources/`
2. Forms: usar `Section`, `Tabs`, `Grid` para organizar campos
3. Tables: incluir `searchable()` e `sortable()` nas colunas relevantes
4. Selects dependentes: usar `->reactive()` com `->options(fn (Get $get) => ...)`
5. Usar `TextColumn`, `BadgeColumn`, `IconColumn` conforme o tipo de dado
6. Incluir filtros de tabela relevantes (status, cliente, período)
7. Incluir Actions para operações (gerar PDF, mudar status, etc)
8. Labels e placeholders em português do Brasil

## Painéis do projeto:
- **Admin** (`/admin`): Acesso completo — todos os Resources
- **Campo** (`/campo`): Operações de campo — ServiceOrder (read-only), Pages customizadas
- **Comum** (`/comum`): Portal do cliente — ServiceOrder e Invoice (read-only, filtrado por client_id)

## Padrões de interface:
- Selects de Estado/Cidade: cidade depende do estado selecionado (reactive)
- Selects de Cliente/Contrato: contrato depende do cliente (reactive)
- Selects de Padrão/Item: item depende do padrão (reactive)
- Status com cores: usar `->color(fn ($state) => match($state) { ... })`
- Datas: formato brasileiro `->date('d/m/Y')`
- Valores monetários: `->money('BRL')`

## Ao criar o Resource:
- Use `php artisan make:filament-resource` com `--generate` se aplicável
- Implemente form() e table() completos
- Crie RelationManagers quando houver hasMany relevante
- Adicione Actions customizadas no header ou na table
- **OBRIGATÓRIO:** A página de listagem (`ListXxx extends ListRecords`) DEVE sempre ter o botão de criar:

```php
use Filament\Actions\CreateAction;

protected function getHeaderActions(): array
{
    return [
        CreateAction::make(),
    ];
}
```

$ARGUMENTS
