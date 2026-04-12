# G-Lux — Levantamento de Requisitos

## 1. Visão Geral

**Sistema:** G-Lux — Gestão de Serviços de Calibração e Ensaios
**Objetivo:** Reescrever sistema legado PHP procedural em Laravel 12+ / Filament 4.x, mantendo funcionalidades existentes e adicionando novos módulos.
**Usuários:** Administradores, Técnicos de Campo, Clientes (portal)

---

## 2. Requisitos Funcionais — Módulos Existentes (Migração)

### RF-01: Gestão de Usuários
- RF-01.1: Cadastrar usuários com nome, email, CPF, login, senha
- RF-01.2: Atribuir roles (admin, campo, comum)
- RF-01.3: Ativar/desativar usuários (soft delete)
- RF-01.4: Forçar troca de senha no primeiro acesso
- RF-01.5: Recuperação de senha por email
- RF-01.6: Vincular usuário "comum" a um cliente

### RF-02: Gestão de Clientes
- RF-02.1: Cadastrar cliente com razão social, nome fantasia, CNPJ, telefone, email
- RF-02.2: Endereço completo (estado, cidade, logradouro)
- RF-02.3: Dados do responsável (nome, telefone, email, celular)
- RF-02.4: Vincular cliente a um padrão de serviço
- RF-02.5: Ativar/desativar clientes
- RF-02.6: Consultar e editar clientes

### RF-03: Gestão de Contratos
- RF-03.1: Cadastrar contratos vinculados a clientes
- RF-03.2: Consultar e editar contratos
- RF-03.3: Ativar/desativar contratos

### RF-04: Gestão de Padrões de Serviço
- RF-04.1: Cadastrar padrões (templates de tipo de serviço)
- RF-04.2: Associar atributos-template ao padrão (standard_attributes)
- RF-04.3: Pré-selecionar valores padrão para atributos tipo select
- RF-04.4: Consultar e editar padrões

### RF-05: Gestão de Atributos
- RF-05.1: Cadastrar atributos com nome e tipo (texto livre ou seleção)
- RF-05.2: Para tipo seleção: cadastrar valores possíveis inline
- RF-05.3: Consultar e editar atributos e valores
- RF-05.4: Ativar/desativar atributos

### RF-06: Gestão de Itens
- RF-06.1: Cadastrar item vinculado a um padrão
- RF-06.2: Campos: nome, quantidade de dígitos (4 ou 6), vencimento (meses), etiqueta, foto
- RF-06.3: Ao criar item, pré-selecionar atributos do template do padrão
- RF-06.4: Permitir ajustar atributos e valores por item individual
- RF-06.5: Associar normas ao item
- RF-06.6: Tudo em uma única tela com abas (dados básicos, atributos, normas)
- RF-06.7: Consultar e editar itens

### RF-07: Gestão de Normas
- RF-07.1: Cadastrar normas técnicas
- RF-07.2: Associar normas a itens
- RF-07.3: Consultar e editar normas

### RF-08: Gestão de Equipamentos
- RF-08.1: Cadastrar equipamentos de medição/calibração
- RF-08.2: Registrar dados de calibração (data, certificado, validade)
- RF-08.3: Controle de prazos de calibração com alertas
- RF-08.4: Consultar e editar equipamentos

### RF-09: Ordens de Serviço
- RF-09.1: Criar OS com: cliente, contrato, estado/cidade, responsável, padrão, datas
- RF-09.2: Selects dependentes: cliente → contrato, estado → cidade, padrão → itens
- RF-09.3: Associar orçamento aprovado à OS
- RF-09.4: Ciclo de vida: Aberta → Em Execução → Concluída → Faturada
- RF-09.5: Listar OS por status com filtros (cliente, período, responsável)
- RF-09.6: Reabrir OS concluída
- RF-09.7: Excluir OS (soft delete)
- RF-09.8: Alterar responsável da OS
- RF-09.9: Notificar por email ao concluir OS

### RF-10: Operações de Campo
- RF-10.1: Registrar temperatura e umidade do ambiente
- RF-10.2: Adicionar itens à OS com quantidade (1-30)
- RF-10.3: Preencher dados de inspeção por item:
  - Formulário dinâmico baseado nos atributos do item
  - Tipo texto: campo livre / Tipo select: dropdown com valores pré-cadastrados
  - Campo "Resultado": Aprovado ou Reprovado
  - Se reprovado: motivo (Visual/Elétrico/Dimensional), descrição, rastreamento anterior
- RF-10.4: Preenchimento em lote: preencher 1x e replicar para N unidades
- RF-10.5: Preenchimento individual: editar unidade específica
- RF-10.6: Gerar código de rastreabilidade automático para aprovados
- RF-10.7: Gerar código de reprovação para reprovados
- RF-10.8: Marcar item como etiquetado
- RF-10.9: Editar item já registrado (UPDATE direto, sem duplicar registros)
- RF-10.10: Edição em massa de itens
- RF-10.11: Visualizar todos os itens da OS (aprovados e reprovados)
- RF-10.12: Fechar OS (marcar como concluída)
- RF-10.13: Gerar resumo de serviço em PDF

### RF-11: Rastreabilidade
- RF-11.1: Faixa de códigos configurável (range_start/range_end) por tipo de dígito (4 ou 6)
- RF-11.2: Geração sequencial automática dentro da faixa
- RF-11.3: Tela admin para configurar faixas
- RF-11.4: Exibir último código gerado e quantidade restante na faixa
- RF-11.5: Alertar quando faixa estiver próxima de esgotar

### RF-12: Relatórios
- RF-12.1: Relatório de serviço (resumo geral)
- RF-12.2: Relatório por item aprovado
- RF-12.3: Relatório por item reprovado
- RF-12.4: Variantes landscape para cada relatório
- RF-12.5: Resumo de serviço em PDF
- RF-12.6: Filtros: cliente, OS, período, padrão
- RF-12.7: Geração em PDF (DomPDF)
- RF-12.8: Header/footer com logo da empresa e paginação

### RF-13: Busca de Certificados
- RF-13.1: Buscar certificado por código de rastreamento
- RF-13.2: Buscar por placa veicular
- RF-13.3: Disponível no portal do cliente (painel Comum)

### RF-14: Logs do Sistema
- RF-14.1: Registrar todas as operações CRUD automaticamente
- RF-14.2: Visualizar logs com filtros (usuário, ação, período, tabela)
- RF-14.3: Dados: quem, quando, o quê, antes/depois

---

## 3. Requisitos Funcionais — Novos Módulos

### RF-15: Orçamentos e Tabela de Preços
- RF-15.1: Cadastrar tabela de preços por cliente vinculada a um padrão
- RF-15.2: Definir preço unitário por item dentro da tabela
- RF-15.3: Definir validade da tabela de preços
- RF-15.4: Criar orçamento selecionando cliente → tabela de preços → itens e quantidades
- RF-15.5: Calcular total automaticamente
- RF-15.6: Ciclo de vida do orçamento: Rascunho → Enviado → Aprovado → Rejeitado → Expirado
- RF-15.7: Enviar orçamento ao cliente por email
- RF-15.8: Disponibilizar orçamento no portal do cliente
- RF-15.9: Vincular orçamento aprovado à OS

### RF-16: Gestão de Faturas (Expandida)
- RF-16.1: Gerar fatura vinculada a uma OS
- RF-16.2: Campos: número, valor, vencimento, status, PDF
- RF-16.3: Upload de PDF da fatura
- RF-16.4: Ciclo de vida: Pendente → Enviada → Vencida → Paga → Cancelada
- RF-16.5: Marcar fatura como paga com data de pagamento
- RF-16.6: Disponibilizar faturas no portal do cliente com download de PDF
- RF-16.7: Listagem com status colorido (verde=paga, amarelo=pendente, vermelho=vencida)
- RF-16.8: Notificar cliente por email ao gerar fatura
- RF-16.9: Notificar automaticamente faturas vencidas (job diário)
- RF-16.10: Dashboard widget com faturas vencidas e a vencer

### RF-17: Relatórios Gerenciais
- RF-17.1: Estrutura extensível para novos tipos de relatório
- RF-17.2: Exportação em PDF e Excel
- RF-17.3: Filtros genéricos (cliente, período, padrão, status)
- RF-17.4: Tipos específicos a serem definidos futuramente

### RF-18: Notificações e Alertas de Validade
- RF-18.1: Calcular data de reteste (data da inspeção + meses de validade do item)
- RF-18.2: Monitorar diariamente itens próximos do vencimento
- RF-18.3: Enviar email consolidado por cliente com itens a vencer
- RF-18.4: Notificação in-app (Filament Notifications) para admin e cliente
- RF-18.5: Configurável: quantos dias antes alertar (ex: 30, 15, 7 dias)
- RF-18.6: Widget no dashboard com contagem de itens a vencer
- RF-18.7: Página de listagem de itens próximos do vencimento

### RF-19: Gestão de Etiquetas com Estoque
- RF-19.1: Registrar entrada de etiquetas no estoque (lote, quantidade, custo)
- RF-19.2: Distribuir etiquetas para equipes/técnicos
- RF-19.3: Registrar consumo de etiquetas por OS (automático ao concluir)
- RF-19.4: Dashboard com visão de estoque: total, distribuído, consumido, disponível
- RF-19.5: Alerta quando estoque abaixo do mínimo
- RF-19.6: Histórico de movimentações por equipe

### RF-20: Chatbot IA (WhatsApp/Telegram)
- RF-20.1: Integrar com WhatsApp Business API e/ou Telegram Bot API
- RF-20.2: Identificar cliente pelo número de telefone
- RF-20.3: Responder consultas sobre laudos/certificados
- RF-20.4: Responder consultas sobre faturas e status
- RF-20.5: Informar próximos serviços agendados
- RF-20.6: Redirecionar para atendente humano quando necessário
- RF-20.7: Registrar histórico de conversas

### RF-21: Comunicação via MCP (Model Context Protocol)
- RF-21.1: Expor a aplicação como servidor MCP via `laravel-boost/mcp`
- RF-21.2: Registrar tools MCP para operações do sistema (consultar OS, buscar rastreamento, listar faturas, etc.)
- RF-21.3: Registrar resources MCP para dados consultáveis (clientes, itens, inspeções)
- RF-21.4: Permitir que agentes de IA (Claude, Copilot, etc.) descubram e invoquem ferramentas do sistema via MCP
- RF-21.5: Autenticação e autorização nos endpoints MCP (respeitar roles do usuário)
- RF-21.6: Integrar com o módulo de Chatbot (RF-20) como backend de dados via MCP

---

## 4. Requisitos Não-Funcionais

### RNF-01: Segurança
- Senhas com bcrypt (migração de MD5 legado com rehash automático)
- CSRF em todos os formulários
- Prepared statements (Eloquent)
- Validação de input via Form Requests
- Rate limiting em login e API
- Roles e permissions (spatie/laravel-permission)

### RNF-02: Performance
- Eager loading para evitar N+1
- Bulk insert para operações em massa (inspections, inspection_values)
- Índices em foreign keys e campos de busca
- Tabelas com ~19M registros suportadas com índices adequados
- Cache para dados estáticos (estados, cidades)

### RNF-03: Usabilidade
- Interface responsiva (Filament já é responsivo)
- Labels e mensagens em português do Brasil
- Datas em formato brasileiro (dd/mm/aaaa)
- Valores monetários em BRL
- Feedback visual com Filament Notifications

### RNF-04: Manutenibilidade
- Código organizado por domínio (Services, Enums, Models)
- Convenções Laravel estritamente seguidas
- Activity log para auditoria
- Documentação de arquitetura e requisitos

### RNF-05: Migração
- Migrar SOMENTE dados mestres: usuários, clientes, padrões, itens, atributos, valores, normas, etiquetas, equipamentos, estados, cidades
- NÃO migrar: ordens de serviço, rastreamentos, dados de serviço, faturas, logs
- Sistema legado permanece em produção como consulta read-only do histórico
- Migração de senhas MD5 com rehash transparente no login

### RNF-06: Extensibilidade
- Arquitetura preparada para novos módulos
- Relatórios como classes plugáveis
- Notificações configuráveis por cliente
- Chatbot como módulo independente
