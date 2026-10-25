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

## Compatibilidade de portaria e estratégia — 25/10/2026

Objetivo: evitar consultas vazias por combinações inexistentes. Ao selecionar município, região, UF ou portaria no diálogo, limitar as estratégias às presentes na API para esse contexto. Se a estratégia anterior deixar de existir, limpar somente o rascunho desse campo e informar a mudança. Aplicar continua sendo obrigatório; cancelar preserva os filtros aplicados. Validar a combinação também ao aplicar, inclusive para consultas antigas. Não alterar bases, rótulos de estratégias ou seleções U/V.

Exemplo correto: Rio De Janeiro/RJ, Portaria 10.345 oferece PAS e Residentes conforme a API conferida nesta data. Evitar: oferecer eSB 40h nessa combinação ou inferir situação municipal a partir de zero resultados. Consultas antigas sem resultados mostram alternativas reais da aba quando disponíveis. Arquivos: `index.html`, `tests/navegacao.cjs`; regressão verifica PAS com 76 credenciadas.

## Etapas visuais de homologação e coluna J — 25/10/2026

Objetivo: mostrar a posição no ciclo e as parcelas ainda previstas, sem confundir calendário com processamento. Aplicar ao consolidado e ao detalhe municipal. A coluna J, transmitida como `tipo`, determina a participação: somente `3 competências` recebe homologações, saldo, percentual e indicador de etapas. `Específicos` exibe somente credenciamento; a coluna V não pode ativar homologação nesses registros. Não alterar Excel ou API.

Exibir três etapas com ordinal (1ª, 2ª, 3ª/última), competência CNES, parcela correspondente e quantidade informada em cada homologação. O título indica a referência do calendário e a próxima etapa; contar parcelas com corte do dia 15 ainda futuro. Informar expressamente que o calendário não comprova processamento. Zero após o corte deve dizer Corte de referência atingido, sem presumir resultado processado. Quando a base encerra a homologação, não anunciar novas etapas. Conclusão total sem excedentes abrevia o indicador na etapa em que o total foi atingido; U/V continuam preservadas. Datas ausentes ou calendários distintos exigem mensagem explicativa, sem eleger um município representante.

Exemplo correto: Portaria 11.772 de julho, referência em 25/10/2026: 2ª homologação, próxima 3ª/última, uma parcela ainda prevista (novembro). Evitar: dizer que houve processamento só porque chegou outubro, ou mostrar homologação em andamento em Específicos. Nas tabelas, substituir métricas de homologação de Específicos por Não se aplica · somente credenciamento. Arquivos: `index.html`, `tests/navegacao.cjs`, documentação de design e regras. Validar dia 14/15, última parcela, conclusão antecipada, coluna J prevalecendo sobre V e seis larguras com dados reais.

### Dois períodos no mesmo indicador — 25/10/2026

Objetivo: permitir que o gestor distinga a ação municipal da verificação ministerial. No consolidado, colocar o acompanhamento em linha própria abaixo dos quantitativos, com identificação da estratégia e dois blocos; no detalhe, repetir os mesmos blocos. Município: mostrar janela CNES M a M+2, limite no último dia de M+2, competência atual e seleção U. Ministério: mostrar parcelas M+2 a M+4, posição no calendário, resultados por etapa e parcelas ainda previstas. Ligar visualmente cada competência CNES, seu limite mensal e a parcela dois meses depois. Destacar separadamente CNES agora e Parcela do mês; podem estar em etapas diferentes. Datas indisponíveis ou divergentes não recebem calendário representativo inferido. Não alterar competências, parcelas, dia 15, U/V ou dados.

Exemplo correto: em 25/10/2026, portaria de agosto: município na 3ª/última competência CNES, limite 31/10/2026; Ministério na 1ª parcela, outubro, verificando agosto. Cadastro de outubro corresponde à parcela dezembro. Evitar: dizer que o prazo municipal acaba em dezembro ou orientar cadastro quando U está encerrado. Calendário expirado com U Ativo exige indicação da divergência, preservando a seleção; nenhuma conclusão sobre processamento decorre apenas da data. Arquivos: index.html, tests/navegacao.cjs, docs/design.md e AGENTS.md. Testar simultaneidade, último dia da janela, dia seguinte e ausência de orientação de cadastro para encerrados.

Os destaques de parcela, limite CNES e quantitativos seguem docs/design.md, seção Destaques e movimento. O pulso identifica ação municipal possível ou parcela do mês com homologação em andamento, sem representar processamento confirmado; ciclos concluídos/encerrados permanecem estáticos e movimento reduzido desativa os efeitos.

### Resultado futuro e explicação narrativa — 25/10/2026

Objetivo: evitar interpretar zero de parcela futura como negativa de homologação. Aplicar ao indicador e ao Explicar. Para tipo 3 competências com V Homologando, quantidade zero e referência anterior ao dia 15 da parcela, mostrar o estado de espera no lugar do número zero: antes/durante a competência CNES, Aguardando competência CNES ou Cadastro CNES em andamento; depois de seu último dia, Em processamento. Informar o mês/ano do resultado previsto, sem prometer dia de publicação. Em processamento designa acompanhamento entre fechamento do CNES e parcela, não confirmação técnica pela API. Manter números positivos, estados encerrados e zeros posteriores ao corte como dados informados; calendário não comprova resultado final. Nenhum dado da fonte ou CSV é alterado.

O Explicar deve contar a sequência: quantidade credenciada, resultado já informado, saldo, prazo municipal, verificação ministerial dois meses depois e espera por cada parcela. Preservar linha do tempo, conclusão antecipada, dados originais, foco e Escape. Exemplo correto: setembro CNES encerrado, parcela novembro futura, mostrar 2ª homologação em processamento e resultado previsto para novembro de 2026; evitar 0 homologadas como destaque ou afirmar que as equipes não foram homologadas. Arquivos: index.html, tests/navegacao.cjs e docs/design.md. Testar dia 14/15, CNES aberto, encerrados, resultados positivos e narrativa do caso Betim.

### Leitura das colunas durante rolagem — 25/10/2026

Objetivo: manter cada quantitativo identificável. Em modo tabela (acima de 1000px), fixar títulos das colunas abaixo do cabeçalho da consulta, com altura medida para acompanhar mudanças de tamanho. Cada cabeçalho fica limitado à própria tabela. No celular, manter os rótulos em cada dado. Não deixar contêiner com overflow oculto impedir o sticky. Exemplo correto: Municípios e Credenciadas continuam visíveis ao rolar entre estratégias; evitar números sem identificação. Arquivos: index.html, tests/navegacao.cjs. Verificar posição real após rolagem e preservar layouts móveis.
