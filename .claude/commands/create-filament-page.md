Crie uma Filament Page customizada para o projeto G-Lux seguindo as convenções:

## Regras obrigatórias:
1. Pages organizadas por painel: `app/Filament/{Admin,Campo,Comum}/Pages/`
2. Usar Livewire para interatividade
3. Views em `resources/views/filament/{painel}/pages/`
4. Labels em português do Brasil
5. Usar Filament Notifications para feedback ao usuário

## Pages planejadas:

### Admin Panel:
- `Dashboard` — Widgets de estatísticas (OS, faturas, alertas)
- `CalibrationControl` — Controle de prazos de calibração de equipamentos
- `SystemLog` — Visualização do activity log (spatie)
- `TraceabilitySettings` — Configuração de faixas de rastreamento
- `Reports` — Seleção e geração de relatórios

### Campo Panel:
- `Dashboard` — Serviços agendados e recentes do técnico logado
- `StartService` — Lista OS abertas com ação "iniciar"
- `AddItems` — Selecionar item do padrão e quantidade
- `FillItemData` — **MAIS COMPLEXA** — Formulário dinâmico baseado nos atributos do item
- `RecordTemperatureHumidity` — Registro de condições ambientais
- `ServiceSummary` — Resumo com itens aprovados/reprovados
- `RejectedItems` — Lista de itens reprovados com motivos
- `BatchEdit` — Edição em massa de itens

### Comum Panel:
- `Dashboard` — Serviços e alertas do cliente
- `CertificateSearch` — Busca de certificados por rastreamento
- `VehicleSearch` — Busca por placa veicular

## FillItemData (referência especial):
Esta é a page mais complexa. O formulário é dinâmico:
- Colunas baseadas nos atributos do item (via item_attribute pivot)
- Tipo 1 (texto) = TextInput | Tipo 2 (select) = Select com valores do item_attribute_value
- Campo "Resultado" controla se é aprovado/reprovado
- Suporte a preenchimento em lote (replicar primeira linha)
- Ao salvar: cria inspections + inspection_values via InspectionService

$ARGUMENTS
