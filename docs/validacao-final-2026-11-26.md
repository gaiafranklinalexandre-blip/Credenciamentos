# Macrorregião e validação do painel — 26/11/2026

## Dados

Após aprovação do usuário das prioridades apresentadas, preenchido exclusivamente o campo `macrorregiao` dos 16.553 registros da API. Fonte: coluna `Macroregião`, aba `Base` da planilha ativa. A auditoria prévia confirmou equivalência integral dos demais campos, com multiplicidade preservada, e das 40 portarias.

O sincronizador existente substitui a tabela de credenciamentos em lotes; por isso foram feitos backup integral da API, checagem de concorrência antes da escrita, verificação integral após o envio e preservação dos hashes das duas planilhas. Somente os valores de negócio de `macrorregiao` mudaram; `id` e `updated_at` são metadados técnicos regenerados por esse fluxo. Portarias e demais ações de sincronização não foram enviadas.

Auditoria posterior: 16.553 registros locais e públicos equivalentes, 40 portarias equivalentes, nenhuma divergência e todas as macrorregiões preenchidas. As homologações já publicadas foram preservadas. O procedimento local `atualizar_macrorregiao.py` permite planejamento sem escrita e exige `--aplicar-autorizado` para aplicar; seis testes isolados verificaram duplicidades, ambiguidades, divergência em homologação, contrato de campos e idempotência. Script, testes e backups continuam locais, fora desta publicação de frontend.

## Acessibilidade e interface

Na árvore de acessibilidade do Chrome, o Core 3.7.0 atribuía `role="combobox"` ao contêiner dos campos gerados, mas o contêiner não recebia nome acessível. O nome foi associado ao rótulo já existente, tanto no adaptador dos filtros quanto no inicializador do Simulador. Opções, valores, eventos, visual e regras de negócio preservados; sem atualização de bibliotecas.

A fonte comum local `02_PAGINAS_ONLINE/design_referencia/govbr/select-adapter.js` recebeu a mesma correção e foi reincorporada somente ao `index.html` deste painel. Nenhum outro produto foi regenerado ou publicado; Totems e Modo Tático preservados.

Referência oficial: [Select — Acessibilidade](https://www.gov.br/ds/components/select?tab=acessibilidade), recomendações de rótulo associado ao campo e nome acessível, consultadas na coleta comum de 21/11/2026.

Verificações executadas:

- Suíte existente: dez larguras de 320 a 1920 px × quatro abas, IDs, grade/rolagem, erros de script, dicas, Simulador por mouse/teclado e navegação das abas por setas.
- Auditoria adicional: árvore de acessibilidade e contraste em oito cenários, incluindo matriz expandida, filtros abertos, botão Limpar com foco/hover, Portarias e Simulador preenchido. Após a correção, nenhum controle sem nome nem texto abaixo do contraste de referência nesses cenários.
- Nu Html Checker: zero erros e um aviso sobre ausência de `h1` nativo; o cabeçalho existente tem `role="heading" aria-level="1"`. O aviso não foi ocultado e não motivou mudança adicional de estrutura.

Limites: não foi executado teste humano com leitor de tela, nem zoom real do navegador. Na tentativa automatizada em sessão isolada, o atalho não mudou o zoom; a cobertura de larguras não é apresentada como zoom real. Os cenários de contraste não cobrem todos os estados possíveis e não certificam WCAG. A auditoria também não comprova cobertura de todas as portarias oficiais.

## Evidências e preservação

Relatórios e backups locais, ignorados pelo Git, em `docs/validacoes-locais/`: auditorias antes/depois, diretório `macrorregiao-20261126-144741-699661/`, resultados de acessibilidade local/público e análise Nu. Nenhum relatório com registros individuais ou credencial acompanha o frontend.

Troca da chave de escrita, senha do banco e instalação de PHP na Hostinger continuam adiadas conforme decisão anterior do usuário. Esta entrega não altera essas configurações nem publica o PHP.
