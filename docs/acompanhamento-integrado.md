# Acompanhamento municipal integrado

## Objetivo e quando aplicar

Desde 24/10/2026, o painel abre com o panorama de todos os municípios e a aba **Credenciamentos ativos** selecionada. As outras abas são **Credenciamentos encerrados**, **Homologações** e **Portarias**. Cada cartão mantém integrados os resultados e os dois estados do processo. Aplicar estas orientações a toda mudança na consulta e nas mensagens de situação.

## Instruções práticas

- Consultar exclusivamente `sync.php?action=data` e `sync.php?action=portarias`. Não incorporar quantitativos, municípios ou datas de portarias fixos no frontend.
- A data da portaria vem da coluna N (`Data`) da base principal, transmitida como `data_portaria`. O registro da base principal prevalece sobre o catálogo de portarias. Sem data válida, informar a impossibilidade de calcular o prazo. Não usar uma captura como substituta da base.
- A coluna U (`Seleção Credenciamento`) determina exclusivamente as abas Ativos e Encerrados. Ativo significa que ainda existe ação municipal no processo de credenciamento; Encerrado significa que não há mais ação municipal de credenciamento. Não substituir essa seleção pelo calendário ou pelo percentual homologado.
- A coluna V (`Seleção homologação`) é independente. `Homologando` indica homologação em andamento; os demais estados são exibidos como informados. Na aba Homologações, oferecer filtros Todas, Em andamento e Encerradas, incluindo credenciamentos já encerrados. Registros específicos não entram nessa aba.
- Agrupar por município, UF, portaria, data, estratégia e tipo; somar as três homologações e calcular o saldo. Preservar os registros originais no CSV.
- Exibir primeiro os casos que precisam de atenção, depois acompanhamento e resultados concluídos dentro de cada aba. Quantidade totalmente homologada não muda a seleção da coluna U ou V. Mostrar separadamente ambos os estados em cada cartão.
- Registros de tipo específico não recebem calendário de três competências nem saldo de homologação inferido.
- Manter competências M, M+1, M+2 e parcelas M+2, M+3, M+4. Usar datas civis de Brasília, incluindo o último dia da janela. A partir do dia 15, o corte de referência já foi atingido; isso não comprova atualização da base.
- Saldo pendente não comprova falta de cadastro. Orientar a conferência do CNES, sem atribuir causa individual não demonstrada.
- O campo `updated_at` informa atualização técnica do registro, não necessariamente a competência efetivamente processada. Não apresentar data de portaria como atualização dos resultados.
- O botão “Entender situação” abre uma linha do tempo visual com publicação, competências CNES e parcelas. Em cada competência, mostrar explicitamente o quantitativo homologado de origem e a parcela financeira correspondente (exemplo: 1 equipe da competência CNES agosto/2026 homologada na parcela outubro/2026). Manter Escape, foco contido e devolução do foco ao botão de origem.
- Preservar autocomplete, filtros por portaria/estratégia, portarias, paginação, CSV e consentimento. CSS e JS permanecem incorporados em `index.html`.

## Exemplos corretos

- “Todas as equipes homologadas”, mesmo com prazo de cadastro encerrado.
- “Aguardando próximas parcelas”, quando a janela fechou e ainda existem parcelas antes do corte final.
- “Credenciamento encerrado · homologação em andamento”, sem instruir cadastro no CNES após encerramento da seleção municipal.
- “Prazo não disponível na base”, quando não há data que permita o cálculo.
- “Confira o cadastro no CNES das equipes ainda não homologadas”, quando existe saldo e janela aberta.

## Exemplos a evitar

- Tudo vermelho porque uma competência terminou.
- “Equipe não cadastrada” deduzido somente do saldo.
- Datas de portaria fixas ou dados de exemplo em produção.
- Chamar zero de resultado definitivo apenas porque o calendário passou do corte.

## Arquivos impactados e validação

`index.html`, `tests/painel.cjs`, `AGENTS.md`, `sync.php` local/Hostinger e documentos de contexto, design e deploy. As planilhas não foram salvas nem alteradas. `sync.php` não é incluído no commit desta revisão.

Executar `node tests/painel.cjs` com Playwright disponível (`CRED_PLAYWRIGHT` pode indicar o módulo). `CRED_TEST_URL` seleciona a URL; padrão local na porta 8963. Verifica panorama com API real, colunas U/V independentes, Betim, competências/parcelas, filtros, paginação, CSV, modal, teclado e quatro larguras, sem coleta de analytics de QA. `tests/integrado.cjs` descreve a interface anterior. Capturas locais em `validacao_visual/`.

## Correção da transmissão de datas — 24/10/2026

A afirmação anterior de data ausente no Excel estava incorreta: a coluna N contém datas válidas nos 16.553 registros; a Portaria 12123 de Betim contém 24/08/2026. O PHP remoto tinha erro na sequência de tipos de `bind_param`: posição 13 (portaria) era texto e posição 14 (data) era inteiro. Corrigidos os tipos conforme os 26 campos, mantendo data como texto e quantitativos como inteiros.

O PHP remoto foi lido, preservado em backup local não versionado e atualizado com somente essa substituição, sem publicar as demais alterações preexistentes do PHP local. O arquivo remoto foi relido para conferir a igualdade com o patch esperado. Todos os campos de negócio foram reconciliados entre uma cópia de leitura do Excel aberto e a API antes da sincronização. Depois, o fluxo existente de `sync.py` recarregou os mesmos 16.553 registros; o retorno foi comparado campo a campo, incluindo todas as datas. Nenhuma estrutura, valor ou fórmula do Excel foi escrita.

Exemplo Betim: CNES agosto → parcela outubro; setembro → novembro; outubro → dezembro. A coluna U controla o prazo de ação municipal, independentemente do ciclo ministerial de homologação da coluna V. A base conferida tem 2 registros Ativos e 16.551 Finalizados; o painel preserva essa classificação, sem ampliar o universo de Ativos por conta própria.
