Crie um Service class para o projeto G-Lux seguindo as convenções:

## Regras obrigatórias:
1. Services em `app/Services/`
2. Injeção de dependência via constructor
3. Métodos públicos com return types
4. Usar DB::transaction() para operações que envolvem múltiplas tabelas
5. Lançar exceções específicas (criar em `app/Exceptions/` se necessário)
6. Documentar métodos complexos com PHPDoc

## Services existentes/planejados:
- `TraceabilityCodeService` — Geração de códigos de rastreamento com faixa configurável
- `ServiceOrderNumberService` — Numeração sequencial de OS por cliente
- `ServiceOrderLifecycleService` — Transições de status da OS
- `InspectionService` — Workflow de aprovação/reprovação e preenchimento em lote
- `ReportService` — Geração de PDFs com DomPDF

## Lógica de negócio crítica:
- **Rastreabilidade**: Consultar `traceability_settings` para faixa. MAX(traceability_code) + 1. Validar range.
- **Inspeções em lote**: Criar N inspections + N*M inspection_values via bulk insert.
- **Transições de OS**: open → in_progress → completed → billed. Validar pré-condições.

## Ao criar o Service:
- Registre no ServiceProvider se usar interface
- Crie testes unitários em `tests/Unit/Services/`
- Use type hints estritos

$ARGUMENTS
