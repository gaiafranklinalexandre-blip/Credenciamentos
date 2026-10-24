# Acompanhamento municipal integrado

## Objetivo e quando aplicar

Desde 24/10/2026, a consulta municipal reúne credenciamento e homologação na mesma página. Substitui as cinco abas antigas e o dashboard. Aplicar estas orientações a toda mudança na consulta e nas mensagens de situação.

## Instruções práticas

- Consultar exclusivamente `sync.php?action=data` e `sync.php?action=portarias`. Não incorporar quantitativos, municípios ou datas de portarias fixos no frontend.
- Resolver datas pelo registro de credenciamento ou pela data única disponível no catálogo de portarias. Sem data válida, informar a impossibilidade de calcular o prazo. Não usar uma captura como substituta da base.
- Agrupar por município, UF, portaria, data, estratégia e tipo; somar as três homologações e calcular o saldo. Preservar os registros originais no CSV.
- Exibir primeiro os casos que precisam de atenção, depois acompanhamento e resultados concluídos. Quantidade totalmente homologada prevalece sobre o encerramento do prazo de cadastro.
- Registros de tipo específico não recebem calendário de três competências nem saldo de homologação inferido.
- Manter competências M, M+1, M+2 e parcelas M+2, M+3, M+4. Usar datas civis de Brasília, incluindo o último dia da janela. A partir do dia 15, o corte de referência já foi atingido; isso não comprova atualização da base.
- Saldo pendente não comprova falta de cadastro. Orientar a conferência do CNES, sem atribuir causa individual não demonstrada.
- O campo `updated_at` informa atualização técnica do registro, não necessariamente a competência efetivamente processada. Não apresentar data de portaria como atualização dos resultados.
- O botão “Entender situação” abre um dialog nativo com publicação, competências, parcelas e orientação. Manter Escape, foco contido e devolução do foco ao botão de origem.
- Preservar autocomplete, filtros por portaria/estratégia, portarias, paginação, CSV e consentimento. CSS e JS permanecem incorporados em `index.html`.

## Exemplos corretos

- “Todas as equipes homologadas”, mesmo com prazo de cadastro encerrado.
- “Aguardando próximas parcelas”, quando a janela fechou e ainda existem parcelas antes do corte final.
- “Prazo não disponível na base”, quando não há data que permita o cálculo.
- “Confira o cadastro no CNES das equipes ainda não homologadas”, quando existe saldo e janela aberta.

## Exemplos a evitar

- Tudo vermelho porque uma competência terminou.
- “Equipe não cadastrada” deduzido somente do saldo.
- Datas de portaria fixas ou dados de exemplo em produção.
- Chamar zero de resultado definitivo apenas porque o calendário passou do corte.

## Arquivos impactados e validação

`index.html`, `tests/integrado.cjs`, `AGENTS.md` e documentos de contexto, design e deploy. Dados, planilhas e backend não foram alterados nesta reformulação.

Executar `node tests/integrado.cjs` com Playwright disponível (`CRED_PLAYWRIGHT` pode indicar o módulo). `CRED_TEST_URL` seleciona a URL; padrão local na porta 8963. Verifica API real, Betim, datas controladas, filtros, portarias, exportação, modal, teclado, quatro larguras e consentimento sem coleta de QA. Capturas locais em `validacao_visual/`.

Limitação verificada em 24/10/2026: API retornou 16.553 registros de credenciamento, todos com data zerada, e 34 portarias. O catálogo não retornou 12123 e 11772. Betim possui eSF 2/1 e eMulti 1/1, mas o prazo da Portaria 12123 não pode ser calculado até a correção das fontes. Corrigir o fluxo de dados separadamente; não compensar no frontend.
