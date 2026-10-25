# Acompanhamento municipal integrado

## Objetivo e quando aplicar

Desde 24/10/2026, o painel abre com o panorama de todos os municípios **consolidado por portaria**, em **Credenciamentos ativos**. O menu lateral oferece também **Credenciamentos encerrados** e **Portarias**. As homologações estão integradas aos credenciamentos; não há área separada de Homologações. Cada portaria apresenta uma tabela por estratégia; os detalhes individuais e a linha do tempo aparecem após escolher um município. Aplicar estas orientações a toda mudança na consulta e nas mensagens de situação.

## Instruções práticas

- Consultar exclusivamente `sync.php?action=data` e `sync.php?action=portarias`. Não incorporar quantitativos, municípios ou datas de portarias fixos no frontend.
- A data da portaria vem da coluna N (`Data`) da base principal, transmitida como `data_portaria`. O registro da base principal prevalece sobre o catálogo de portarias. Sem data válida, informar a impossibilidade de calcular o prazo. Não usar uma captura como substituta da base.
- A coluna U (`Seleção Credenciamento`) determina exclusivamente as abas Ativos e Encerrados. Ativo significa que ainda existe ação municipal no processo de credenciamento; Encerrado significa que não há mais ação municipal de credenciamento. Não substituir essa seleção pelo calendário ou pelo percentual homologado.
- A coluna V (`Seleção homologação`) é independente. `Homologando` indica homologação em andamento; os demais estados são exibidos como informados nos próprios credenciamentos. Credenciamentos encerrados continuam mostrando homologação em andamento quando indicado na base, sem precisar de uma área separada. Registros específicos não recebem um ciclo de homologação inferido.
- Na visão geral, agrupar por número e ano da portaria, depois por estratégia e tipo. Mostrar número de municípios distintos, credenciadas, homologadas, saldo e situação. Não representar o consolidado usando o estado de apenas um município.
- Somar as três homologações de cada registro. No consolidado, somar os saldos individuais não negativos; excedente de homologação de um registro não pode compensar pendência de outro. Sinalizar quantitativos excedentes para conferência, sem alterar a fonte. Tipos específicos não recebem homologação ou saldo inferido.
- Após **Saldo**, mostrar **% de implantação = homologadas ÷ credenciadas × 100**, com duas casas decimais e formatação brasileira. Na visão consolidada, dividir a soma das homologadas pela soma das credenciadas do grupo, nunca fazer média simples dos percentuais municipais. No detalhe municipal, usar os quantitativos daquele registro/grupo. Denominador zero ou tipo sem ciclo de homologação: “—”. Excedentes acima de 100% permanecem visíveis junto da sinalização de conferência dos quantitativos; não truncar dados.
- No detalhe municipal, manter o agrupamento por município, UF, portaria, data, estratégia e tipo, preservando seleções U/V distintas. O CSV continua contendo os registros originais da aba e dos filtros, sem substituir as linhas pelas agregações visuais.
- Manter **somente um KPI: Credenciamentos ativos**, soma da quantidade credenciada em registros com coluna U Ativo, respeitando os filtros de município, região, UF, portaria e estratégia. A aba selecionada e o filtro de situação da homologação não mudam a definição desse KPI.
- Dar destaque visual ao número da portaria e ao nome da estratégia, tanto no consolidado quanto nos detalhes. “Ver municípios” abre lista pesquisável e paginada da portaria/estratégia; ao escolher um município, preservar a área e aplicar os filtros correspondentes.
- Não exibir a antiga barra de pesquisa inicial. Centralizar Região, UF, Município, Portaria e Estratégia no botão **Filtros**, que abre um diálogo lateral. Região e UF restringem as opções municipais. O município tem autocomplete com teclado e destaque do trecho encontrado.
- Alterações no diálogo são rascunhos: só entram em vigor ao clicar em **Aplicar filtros**. Fechar ou pressionar Escape descarta o rascunho. Nome municipal ambíguo ou inexistente exige seleção válida, sem aplicar uma consulta incorreta. “Limpar filtros” no diálogo limpa o rascunho; os chips e “Limpar todos” no painel removem filtros aplicados.
- Mostrar o contexto atual no título, no caminho **Consolidado → Município / UF** e nos chips de filtros. No município, manter **← Voltar ao consolidado** no cabeçalho fixo. O retorno restaura filtros e paginação da origem, mantendo a área atual; não deve redefinir tudo para Ativos.
- A troca de área pelo menu lateral preserva os filtros e o município selecionado. Os botões Voltar/Avançar do navegador restauram o estado da consulta; recarregar preserva o estado da entrada atual no histórico. Esse histórico contém apenas parâmetros de navegação, nunca cópias dos dados da API.
- No computador o menu permanece lateral. No celular, usar gaveta com botão de menu, fundo de fechamento, Escape, foco contido e indicação da área atual. O menu fechado deve ficar fora da ordem de tabulação. Preservar foco na saída dos diálogos.
- Exibir primeiro os casos que precisam de atenção, depois acompanhamento e resultados concluídos dentro de cada aba. Quantidade totalmente homologada não muda a seleção da coluna U ou V. Mostrar separadamente ambos os estados em cada cartão.
- Registros de tipo específico não recebem calendário de três competências nem saldo de homologação inferido.
- Manter competências M, M+1, M+2 e parcelas M+2, M+3, M+4. Usar datas civis de Brasília, incluindo o último dia da janela. A partir do dia 15, o corte de referência já foi atingido; isso não comprova atualização da base.
- Saldo pendente não comprova falta de cadastro. Orientar a conferência do CNES, sem atribuir causa individual não demonstrada.
- O campo `updated_at` informa atualização técnica do registro, não necessariamente a competência efetivamente processada. Não apresentar data de portaria como atualização dos resultados.
- O botão **Explicar** abre uma linha do tempo visual com publicação, competências CNES e parcelas. Em cada competência, mostrar explicitamente o quantitativo homologado de origem e a parcela financeira correspondente (exemplo: 1 equipe da competência CNES agosto/2026 homologada na parcela outubro/2026). Se o total homologado for igual ao credenciado, mostrar apenas até a competência na qual o acumulado atingiu o total e encerrar com **✓ Concluído**. Não mostrar competências posteriores desnecessárias. Excedentes ou saldo pendente não permitem abreviar a linha do tempo. Isso não altera as seleções originais U/V. Manter Escape, foco contido e devolução do foco ao botão de origem.
- Usar verde e sinal de conclusão para homologação total; azul e indicação de espera para processamento; âmbar/laranja, ícone de atenção e prazo quando houver ação municipal pendente; cinza para encerramento sem ação municipal. Cor nunca é o único indicador. Não usar vermelho apenas porque uma competência passou.
- Preservar autocomplete, filtros por portaria/estratégia, portarias, paginação, CSV e consentimento. CSS e JS permanecem incorporados em `index.html`.

## Exemplos corretos

- “Todas as equipes homologadas”, mesmo com prazo de cadastro encerrado.
- Portaria 12.123 consolidada: eMulti Estratégica 1/1, concluída; eSF 1/2, saldo 1. O município só aparece como detalhamento da consulta.
- Linha do tempo da eMulti de Betim: credenciamento → CNES agosto / parcela outubro → Concluído, sem setembro e outubro CNES posteriores.
- Betim/eSF: 1 homologada de 2 credenciadas = 50,00% de implantação; eMulti: 1 de 1 = 100,00%.
- “Aguardando próximas parcelas”, quando a janela fechou e ainda existem parcelas antes do corte final.
- “Credenciamento encerrado · homologação em andamento”, sem instruir cadastro no CNES após encerramento da seleção municipal.
- “Prazo não disponível na base”, quando não há data que permita o cálculo.
- “Confira o cadastro no CNES das equipes ainda não homologadas”, quando existe saldo e janela aberta.

## Exemplos a evitar

- Tudo vermelho porque uma competência terminou.
- Listar milhares de cartões municipais na visão geral ou exibir outros KPIs além de credenciamentos ativos.
- Deixar o gestor sem retorno visível ao consolidado, limpar portaria/estratégia ao trocar de área ou aplicar filtros antes da confirmação no diálogo.
- “Equipe não cadastrada” deduzido somente do saldo.
- Datas de portaria fixas ou dados de exemplo em produção.
- Chamar zero de resultado definitivo apenas porque o calendário passou do corte.

## Arquivos impactados e validação

`index.html`, `tests/navegacao.cjs` (com entradas de compatibilidade em `tests/consolidado.cjs`, `tests/painel.cjs` e `tests/integrado.cjs`), `AGENTS.md` e documentos de contexto, design e deploy. A revisão de navegação não altera planilhas, dados, regras de negócio ou backend. A correção anterior do PHP está registrada abaixo.

Executar `node tests/navegacao.cjs` com Playwright disponível (`CRED_PLAYWRIGHT` pode indicar o módulo). `CRED_TEST_URL` seleciona a URL; padrão local na porta 8964. Verifica API real, consolidação, percentual de implantação (inclusive 0%, 50%, 100%, excedentes e denominador zero), independência U/V, único KPI, três áreas do menu, filtros com município, validação, cancelar/aplicar, menu lateral/móvel, retorno explícito e do navegador, preservação da consulta ao recarregar, restauração da paginação, Explicar, conclusão abreviada, CSV, teclado e seis larguras. Analytics permanece recusado durante QA. Capturas locais em `validacao_visual/`.

## Correção da transmissão de datas — 24/10/2026

A afirmação anterior de data ausente no Excel estava incorreta: a coluna N contém datas válidas nos 16.553 registros; a Portaria 12123 de Betim contém 24/08/2026. O PHP remoto tinha erro na sequência de tipos de `bind_param`: posição 13 (portaria) era texto e posição 14 (data) era inteiro. Corrigidos os tipos conforme os 26 campos, mantendo data como texto e quantitativos como inteiros.

O PHP remoto foi lido, preservado em backup local não versionado e atualizado com somente essa substituição, sem publicar as demais alterações preexistentes do PHP local. O arquivo remoto foi relido para conferir a igualdade com o patch esperado. Todos os campos de negócio foram reconciliados entre uma cópia de leitura do Excel aberto e a API antes da sincronização. Depois, o fluxo existente de `sync.py` recarregou os mesmos 16.553 registros; o retorno foi comparado campo a campo, incluindo todas as datas. Nenhuma estrutura, valor ou fórmula do Excel foi escrita.

Exemplo Betim: CNES agosto → parcela outubro; setembro → novembro; outubro → dezembro. A coluna U controla o prazo de ação municipal, independentemente do ciclo ministerial de homologação da coluna V. A base conferida tem 2 registros Ativos e 16.551 Finalizados; o painel preserva essa classificação, sem ampliar o universo de Ativos por conta própria.
