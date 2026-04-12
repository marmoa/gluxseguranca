Consulte o código legado do sistema G-Lux para entender como uma funcionalidade específica foi implementada originalmente.

## Arquivos-chave do sistema legado:

### Lógica de negócio:
- `pages/geral/functions.php` (1789 linhas) — TODAS as funções utilitárias, rastreabilidade, numeração, validações
- `pages/geral/func-inserir.php` — Funções de INSERT (cadastrar item, cliente, atributo, etc)
- `pages/geral/func-update.php` — Funções de UPDATE (quantidade, status, etc)
- `pages/geral/func-deletar.php` — Funções de DELETE
- `pages/geral/func-carregaTabelas.php` (~103KB) — TODAS as queries de listagem e tabelas
- `pages/geral/func-popularSelects.php` — Funções para popular dropdowns
- `pages/geral/func-montaBotoes.php` — Geração de botões de ação
- `pages/geral/func-enviarEmails.php` — Envio de emails (ordem concluída, fatura)

### Telas Admin:
- `pages/admin/*.php` — 78 arquivos (cadastros, consultas, edições, detalhes)
- `pages/admin/ajax-*.php` — 10 endpoints AJAX (cidades, contratos, itens, atributos)

### Telas Campo:
- `pages/campo/preenchendoItens.php` — Formulário dinâmico de preenchimento (MAIS COMPLEXO)
- `pages/campo/adicionaItens.php` — Adicionar itens à OS
- `pages/campo/insereQuantidadeItensOS.php` — Registrar quantidade
- `pages/campo/edicaoEmMassa.php` — Edição em massa

### Banco de dados:
- `pages/banco_de_dados/*.php` — 35 arquivos de INSERT/UPDATE/DELETE

### Relatórios PDF:
- `pages/geral/relatorio.php`, `relatorioPorItem.php`, `relatorioPorItemReprovado.php`
- `pages/geral/resumoServicoPDF.php` — Resumo de serviço em PDF

## Ao consultar o legado:
- Leia o arquivo relevante
- Identifique a lógica de negócio (validações, cálculos, fluxo)
- Mapeie para a estrutura Laravel/Filament
- Aponte diferenças entre o modelo legado e o novo (ex: dados_servico → inspection_values)

$ARGUMENTS
