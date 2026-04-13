# Pendências de Implementação — G-Lux Segurança

> Este arquivo registra todas as pendências que ainda não foram implementadas.
> **IMPORTANTE:** Este arquivo DEVE ser atualizado sempre que uma tarefa for concluída (remover daqui) ou identificada (adicionar aqui).
> Ao concluir uma pendência, mova-a para o `historico.md` com a data de conclusão.

---

## Fase 1 — Fundação ✅ Concluída em 12/04/2026

## Fase 2 — Dados Mestres ✅ Concluída em 12/04/2026

## Fase 3 — Orçamentos e Preços ✅ Concluída em 12/04/2026

## Fase 4 — Ordens de Serviço ✅ Concluída em 12/04/2026

## Fase 5 — Operações de Campo ✅ Concluída em 12/04/2026

> Pendências não implementadas desta fase (descartadas por simplificação de escopo):
> - StartService (absorvida pelo fluxo AddItems → FillItemData)
> - RecordTemperatureHumidity (será adicionada quando necessário — OS já tem campos temperature/humidity)
> - BatchEdit (será adicionada quando necessário)

## Fase 6 — Etiquetas com Estoque ✅ Concluída em 12/04/2026

> Pendências não implementadas (deixadas para Fase 9 — Polish):
> - Consumo automático de etiquetas ao concluir OS (observer no ServiceOrder)

## Fase 7 — Financeiro Expandido

- [ ] Migration: invoices (expandida)
- [ ] Model: Invoice
- [ ] Enum: InvoiceStatus (pending, sent, overdue, paid, cancelled)
- [ ] InvoiceResource (Admin) — CRUD, upload PDF, actions
- [ ] InvoiceResource (Comum) — read-only, download PDF, status colorido
- [ ] Mail: InvoiceCreated
- [ ] Mail: InvoiceOverdue
- [ ] Scheduled Job: CheckOverdueInvoices
- [ ] Widget: InvoiceStatsWidget
- [ ] Testar: gerar fatura → enviar → marcar como paga

## Fase 8 — Relatórios e Alertas

- [ ] ReportService com método genérico
- [ ] Blade templates: service-summary, traceability-report, per-item-report, rejection-report + variantes landscape
- [ ] Blade partial: header-footer com logo
- [ ] Page Admin: Reports (seleção de tipo + filtros)
- [ ] Exportação Excel via maatwebsite/excel
- [ ] Migration: notification_settings
- [ ] Model: NotificationSetting
- [ ] Scheduled Job: CheckExpiringInspections
- [ ] Mail: ExpiringItemsAlert
- [ ] Widget: ExpiringInspectionsWidget (Admin e Comum)
- [ ] Page Admin: ExpiringItems
- [ ] Testar: gerar todos os 7 tipos de PDF + alertas de validade

## Fase 9 — Polish e Go-Live

- [ ] Dashboard Admin com todos os widgets
- [ ] Dashboard Campo personalizado por técnico
- [ ] Dashboard Comum personalizado por cliente
- [ ] Activity logging (spatie) configurado em todos os models
- [ ] Page Admin: SystemLog
- [ ] Mail: ServiceOrderCompleted
- [ ] Mail: QuoteSent
- [ ] Artisan Command: migrate:legacy-data (SOMENTE dados mestres)
- [ ] Artisan Command: validate:legacy-migration
- [ ] Testes Feature para cada Resource
- [ ] Testes Unit para cada Service
- [ ] UAT com usuários reais
- [ ] Deploy em produção

## Fase 10 — MCP + Chatbot IA (Pós Go-Live)

### Servidor MCP (Model Context Protocol)
- [x] ~~Instalar `laravel/mcp` e `laravel/boost`~~ ✅ Concluído em 12/04/2026
- [ ] Registrar MCP Tools: search_certificate, list_invoices, list_service_orders, get_service_summary, check_item_validity
- [ ] Registrar MCP Tools (admin): create_quote, update_order_status, generate_report
- [ ] Registrar MCP Resources: clients, items, standards, inspections/{code}
- [ ] Autenticação e autorização nos endpoints MCP (respeitar roles)
- [ ] Testar: agente IA conecta e invoca tools com sucesso

### Chatbot IA (WhatsApp/Telegram)
- [ ] Migration: chatbot_conversations
- [ ] Migration: chatbot_messages
- [ ] Models: ChatbotConversation, ChatbotMessage
- [ ] ChatbotService (utiliza MCP como backend)
- [ ] ChatbotWebhookController
- [ ] Integração WhatsApp Business API (Twilio ou Meta)
- [ ] Integração Telegram Bot API
- [ ] Integração com Claude API ou OpenAI para NLP
- [ ] Fluxos: consulta de laudos, faturas, próximos serviços
- [ ] Testar: conversa completa via WhatsApp e Telegram

---

## Pendências Avulsas (não vinculadas a fase)

_Nenhuma no momento_
