Crie uma migration Laravel para a tabela solicitada seguindo as convenções do projeto G-Lux:

## Regras obrigatórias:
1. Tabelas e campos em inglês (convenção Laravel)
2. Sempre incluir `$table->softDeletes()` em tabelas de negócio
3. Sempre incluir `$table->timestamps()`
4. Foreign keys com `->constrained()` e cascade adequado
5. Usar enums como string columns (cast no Model)
6. Índices nas foreign keys e campos de busca frequente
7. Campos `is_active` como `boolean()->default(true)`

## Tabelas do projeto (referência):
- users, clients, client_contracts, standards, items, attributes, attribute_values
- item_attribute (pivot), item_attribute_value (pivot), item_norm (pivot)
- standard_attributes, standard_attribute_values
- tags, norms, equipment, states, cities
- service_orders, service_order_items, service_order_numbers, quote_numbers
- inspections, inspection_values, traceability_settings
- invoices, quotes, quote_items, price_tables, price_table_items
- tag_inventory, tag_distributions, tag_consumptions
- notification_settings, chatbot_conversations, chatbot_messages

## Ao criar a migration:
- Use `php artisan make:migration` para gerar o arquivo
- Implemente o schema completo com up() e down()
- Adicione comentários nos campos que não são auto-explicativos

$ARGUMENTS
