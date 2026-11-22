# Checklist gov.br Design System — Painel de Credenciamentos

> **Complemento de 21/11/2026:** a coleta central em `02_PAGINAS_ONLINE/design_referencia/govbr/documentacao/` agora inclui as abas Códigos e Acessibilidade do site oficial, além dos exemplos HTML/CSS/JavaScript. Ler o índice e o guia comum antes de novo ajuste. A limitação de extração descrita abaixo é o histórico da consulta de 20/11; não se aplica à nova coleta. Isto não equivale a reauditar ou marcar o painel como conforme. As regras e resultados anteriores são preservados.


Data da consulta: 20/11/2026. Versão do DS: **3.7.0** (cabeçalho do site: "Design System | Versão 3.7.0"; pacote `@govbr-ds/core@3.7.0`, `latest` no npm; `next` = 4.0.0-next.11, pré-lançamento). Escopo: HTML/CSS/JS puro do `index.html`, **sem a logo gov.br** (marca SUS do projeto).

## Fonte e como ler este arquivo

- **Fonte principal agora: o site `https://www.gov.br/ds/` renderizado em navegador (Edge via Playwright)**; 96 páginas salvas em texto (a 1ª linha de cada arquivo é a URL). O `WebFetch` não lê o site (SPA), por isso a leitura foi feita nesses arquivos.
- Notação de origem: `S:<caminho>` = `https://www.gov.br/ds/<caminho>` (regra lida no site; ex.: `S:fundamentos-visuais/cores`). `npm:<x>` = artefato do pacote `@govbr-ds/core@3.7.0` no jsDelivr/npm (`tokens` = `dist/core-tokens.css`; `ex:<nome>` = `dist/components/<nome>/examples.html`), usado **só onde o site não traz o dado** (marcação HTML/classes). `projeto` = regra do `AGENTS.md`/`CLAUDE.md` do painel. `[Pendente de confirmação]` = não lido/não verificável.
- **Limite da raspagem:** nas páginas de componente o texto capturado traz só a aba "Visão Geral/Uso" (os rótulos "Códigos" e "Acessibilidade" aparecem, mas o conteúdo dessas abas não foi capturado). Logo, **classes HTML, atributos ARIA e comportamento de teclado por componente continuam vindo do `npm:ex:` e não estão confirmados pelo site**.
- Primeira auditoria **estática** (leitura do código, sem navegador nem leitor de tela) do `prototipo/index.html` feita em 21/11/2026; resultado no relatório entregue ao agente que a pediu. Contraste, alturas de alvo e quebra de layout em 320/390/768/1280px **não foram medidos**. Os `examples.html` do npm têm IDs duplicados e erros de digitação; as regras não os copiam.
- Citações de Web Components (WBC) vêm do site (`S:como-comecar/fluxo-dev`) e da documentação do repositório oficial `gitlab.com/govbr-ds/bibliotecas/wbc/govbr-ds-wbc` (`apps/site/docs`, lida em 20/11/2026; ver seção 7). A versão 2.7.0 foi informada pelo usuário e **não aparece nesses arquivos**; os blocos gerados por componente (props, parts, teclado) não estão no repositório e **não foram lidos**.

## Divergências encontradas em relação à versão anterior deste checklist

| # | Antes (marcado como assumido) | O que o site diz | Efeito |
|---|---|---|---|
| D1 | Carregar fonte do CDN do DS (`cdngovbr-ds.estaleiro.serpro.gov.br`) e `core` pelo jsDelivr | `S:como-comecar/fluxo-dev`: nas versões recentes **evitar o CDN próprio do DS**; priorizar npm ou arquivos locais; "Uso de CDN" no site = CDN próprio do projeto. A doc oficial do WBC (`W:cdn`, `W:carregamento-e-csp`, ver seção 7) **recomenda o jsDelivr** para `@govbr-ds/core` e `@govbr-ds/webcomponents` e manda "fixar uma versão explícita; não usar `latest` em produção" (os exemplos usam `@3`/`@2`; o painel usa versão exata, ex.: `@3.7.0`) | **Resolvida:** Estaleiro fora; jsDelivr com versão exata é prática documentada pelo projeto oficial e compatível com a regra 5 (sem arquivo novo). SRI não é tratado (regra 6 pendente) |
| D2 | `<script src=".../core.min.js">` comum e "Tabs/accordion/select funcionam" sozinhos | Script como `type="module"` no fim do `<body>`; **"alguns componentes exigem inicialização manual"** (`new core.BRBreadcrumb('br-breadcrumb', el)`; `new core.Collapse(config).setBehavior()`), só depois de carregar `core.min.js`; inicializar também dependências antes | Regra 4 reescrita. Tabelas geradas por JS precisam instanciar o comportamento depois de renderizar (inferência; ver 111) |
| D3 | Raleway/Google Fonts do projeto vistos como conflito com Rawline | Rawline é a oficial; **Raleway é o fallback aceito** (`S:fundamentos-visuais/tipografia`; `S:como-comecar/fluxo-dev`). Rawline: baixar em Downloads de assets (`S:downloads/assets`) | Regra 9 resolvida: Rawline principal, Raleway reserva |
| D4 | Font Awesome 5.11.2 "URL pendente" | URL oficial do exemplo: `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css`; a página de iconografia cita a versão **5.10.2** (inconsistência interna do site) | Regra 3 atualizada |
| D5 | Espaçamento: "qualquer valor da escala de 4px" | Há **duas** escalas: **Layout** (8px, `base`, `2x`…`10x`) para componentes/posicionamento e **Ajuste** (4px: `half`, `baseh`, `2xh`, `3xh`, `4xh`, `5xh`) **só para textos e ícones**. O site documenta `2xh`…`5xh`; os tokens `6xh`–`10xh` existem no pacote npm mas **não** na página do site | Regra 30 corrigida |
| D6 | "Subabas Ativos/Encerrados: mesmo componente aninhado" (142) | `S:components/tab`: **não usar Tabs dentro de Tabs**; usar `List` para subdividir | Regra 142 corrigida; rever se o painel aninha abas |
| D7 | Tooltip com `aria-describedby` como componente a usar (200/201) | `S:components/tooltip`: componente **obsoleto/depreciado** em 3.7.0 ("uso desencorajado", depende de Popper, será removido). Outras páginas ainda mandam usar tooltip (tag de status, texto truncado) — contradição interna do site | Regra 200 reescrita; decisão do projeto pendente |
| D8 | Accordion como componente (170) | `S:components/accordion`: componente **obsoleto/depreciado**; usar **Utilitário JS > Accordion** e o padrão **Collapse** | Regra 170 reescrita |
| D9 | "O DS 3.7.0 não tem autocomplete" (154) | `S:components/input` tem a seção **Autocomplete** (recurso do input) e `S:components/select` descreve filtro por digitação ("autocomplete") | Regra 154 corrigida: existe orientação de uso; **não** há componente/ARIA próprios nos textos lidos |
| D10 | Estrutura de página sempre com breadcrumb (300) | `S:components/breadcrumb`: "não recomendada para sites sem agrupamento ou hierarquia" | Regra 105/300 viram condicionais; o painel é uma página com abas |
| D11 | Breakpoints "container 536/952/1200/1560" | `S:fundamentos-visuais/grid`: 5 breakpoints (0/576/992/1280/1600), **max-width** 559 / 911 ou 1199 / 1519 / ≥1520, margens e gutters abaixo | Regra 40 atualizada; os valores de container do npm não estão na página |
| D12 | Foco: "deslocamento 4px" por `--focus-offset` | `S:fundamentos-visuais/estados`: borda `--surface-width-lg` (4px) tracejada e **espaçamento de segurança `--spacing-scale-half` (4px)**; cores `--gold-vivid-40` (claro) e `--gold-vivid-20` (escuro) | Regra 60 consistente; nomes de token do site prevalecem |

## 1. Carregamento do DS

Opção de versão fixa e sem build (jsDelivr é o CDN recomendado pela doc oficial do WBC; o site `gov.br/ds` prioriza npm/arquivos locais; ver D1):

```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@govbr-ds/core@3.7.0/dist/core.min.css">
<!-- Rawline: arquivos de S:downloads/assets; sem ela cai em Raleway e sans-serif -->
<script type="module" src="https://cdn.jsdelivr.net/npm/@govbr-ds/core@3.7.0/dist/core.min.js"></script>
```

Alternativa leve oficial: `core-lite.css` (cores Blue, Blue Warm, Blue Warm Vivid, Gold Vivid, Gray, Green Cool Vivid, Pure, Red Vivid, Red Warm Vivid, Yellow Vivid; espaçamentos 0–6 = 0/4/8/16/24/32/40px); todos os tokens seguem disponíveis. `core.min.css` é a versão completa "recomendada para a maioria" (`S:como-comecar/fluxo-dev`).

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 1 | Carregamento | Fixar versão exata do `@govbr-ds/core`; nunca `@latest`/`@next`. O site recomenda npm ou arquivos locais; jsDelivr com versão exata é o CDN recomendado pela doc oficial do WBC (D1) | S:como-comecar/fluxo-dev; npm | Procurar `@govbr-ds/core@` com versão exata |
| 2 | Carregamento | Fonte **Rawline** como família única do DS (`--font-family-base`), com fallback **Raleway** e `sans-serif`. Obter Rawline em Downloads/assets | S:fundamentos-visuais/tipografia; S:como-comecar/fluxo-dev | Computed font-family; Network sem 404 |
| 3 | Carregamento | Ícones: **Font Awesome** estilos `fas` (solid) e `fab` (brand); carregar bibliotecas externas **antes** dos arquivos do DS | S:fundamentos-visuais/iconografia; S:como-comecar/fluxo-dev | Ordem dos `<link>`; ícones renderizam |
| 4 | Carregamento | `core.min.js` como `type="module"` no fim do `<body>`; componentes com comportamento (tab, collapse, select, breadcrumb, pagination, step etc.) exigem **inicialização manual** após o carregamento; inicializar após o DOM pronto e dependências primeiro. Se o bundle embute Popper: [Pendente de confirmação] | S:como-comecar/fluxo-dev | Console sem erro; componentes reagem |
| 5 | Carregamento | Sem dependência nova sem checar Windows/OneDrive e a regra do painel "tudo em `index.html`": usar CDN em vez de arquivos novos | projeto | `git diff` sem novos .css/.js |
| 6 | Carregamento | SRI/integridade e fallback offline do CDN: não tratados no site | [Pendente de confirmação] | — |
| 7 | Carregamento | `<!doctype html>`, `<html lang="pt-BR">`, `<meta charset="UTF-8">`, `<meta name="viewport" content="width=device-width, initial-scale=1.0">` e `<title>` | S:guias/boas-praticas-de-html | Inspecionar `<head>` |
| 8 | Carregamento | Navegadores mínimos do core: Chrome 49, Edge 14, Firefox 67, Safari 11; sem IE/Edge Legacy; JS desabilitado pode quebrar componentes | S:guias/navegadores-suportados | Testar Chrome/Edge/Firefox atuais |
| 9 | Carregamento | Reconciliado: Rawline principal, Raleway reserva (ver D3). Atualizar `CLAUDE.md`/`docs/design.md` que citam só Raleway | S:fundamentos-visuais/tipografia | Ler `docs/design.md` |

## 2. Fundamentos visuais

### 2.1 Cores (`S:fundamentos-visuais/cores`)

Princípios do site: usar **sempre** as cores pré-definidas; cor nova só com teste de acessibilidade e aprovação da equipe do DS. Paleta adaptada do USWDS; nomes = família + luminância (ex.: Blue Warm Vivid 70).

| Função | Token e valor (site) |
|---|---|
| Superfície, fundo claro | `--pure-0` #ffffff (principal); `--gray-2` #f8f8f8 (alternativa) |
| Superfície, fundo escuro | `--blue-warm-vivid-90` #071D41 (principal); `--blue-warm-vivid-80` #0C326F |
| Leitura (texto/ícone) | fundo claro `--gray-80` #333333; fundo escuro `--pure-0` |
| Interação | fundo claro `--blue-warm-vivid-70` #1351B4; fundo escuro `--blue-warm-20` #C5D4EB |
| Informação | `--blue-warm-vivid-60` #155BCB; alternativa `--blue-warm-vivid-10` #D4E5FF |
| Sucesso | `--green-cool-vivid-50` #168821; alternativa `--green-cool-vivid-5` #E3F5E1 |
| Alerta | `--yellow-vivid-20` #FFCD07; alternativa `--yellow-vivid-5` #FFF5C2 |
| Erro | `--red-vivid-50` #E52207; alternativa `--red-vivid-10` #FDE0DB |

Neutros úteis (npm:tokens): `--gray-1…90` (ex.: 5 #f0f0f0, 20 #ccc, 40 #888, 80 #333). O site cita `--gray-40` como cor padrão de borda de superfície e de input.

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 10 | Cores | Usar `var(--token)` do DS; hex solto só se for valor da paleta; cor fora da paleta só testada e justificada | S:fundamentos-visuais/cores | Buscar `#[0-9a-f]{3,6}` no CSS e comparar |
| 11 | Cores | Cor de interação (links, aba ativa, botão primário) = `--blue-warm-vivid-70`; evitar essa cor e variações em elementos não interativos | S:fundamentos-visuais/cores; S:fundamentos-visuais/estados | Valores computados |
| 12 | Cores | Texto padrão `--gray-80` #333 sobre fundo claro; branco sobre fundo escuro | S:fundamentos-visuais/cores | Computed color |
| 13 | Cores | Farol/avisos: verde `--green-cool-vivid-50`, amarelo `--yellow-vivid-20`, vermelho `--red-vivid-50`, azul de informação `--blue-warm-vivid-60`; cinza só para neutro/encerrado (projeto) | S:fundamentos-visuais/cores; projeto | Cada estado usa o token semântico |
| 14 | Cores | A cor **nunca** é o único meio de informação: o próprio site diz que os estilos de estado não dependem só da cor; avisos têm **cor + ícone** | S:fundamentos-visuais/estados; projeto | Escala de cinza: estado segue legível |
| 15 | Cores | Contraste mínimo **WCAG 2.1 AA**: texto normal 4,5:1; texto grande (≥24px, ou ≥19px em negrito) 3:1; ícones e bordas de componente seguem a regra de texto grande (3:1). Algumas cores da paleta só servem a 3:1 (gráficos/texto grande). Tabela de contraste do DS existe (não lida). Atenção: texto escuro sobre `--yellow-vivid-20`; cinzas claros falham sobre branco | S:fundamentos-visuais/cores | Medir cada par texto/fundo do farol, tags e KPI |
| 16 | Cores | Variantes alternativas (#E3F5E1, #FFF5C2, #FDE0DB, #D4E5FF) como fundo suave de mensagens e tags | S:fundamentos-visuais/cores | Fundos de alerta |
| 17 | Cores | Em cada elemento decidir o uso sobre fundo claro **e** escuro; fundo claro = texto previsto escuro | S:fundamentos-visuais/cores; S:fundamentos-visuais/estados | Revisão de temas |
| 18 | Cores | Marca-texto (`mark`): cor `--gray-80`, fundo `--red-warm-vivid-10` (usar no destaque de letras do autocomplete) | S:fundamentos-visuais/tipografia | Inspecionar `<mark>` |

### 2.2 Tipografia (`S:fundamentos-visuais/tipografia`)

Família única Rawline (`--font-family-base`); pesos `--font-weight-thin…black` (100–900); base **14px = 1em**, escala **Minor Third (1,2)**: `--font-size-scale-up-01` 16,8 · `-02` 20,16 · `-03` 24,19 · `-04` 29,03 · `-05` 34,84 · `-06` 41,8 · `-07` 50,16 · `scale-down-01` 11,67 · `-02` 9,72 · `-03` 8,10px. Entrelinha: `low` 1,15 (títulos), `medium` 1,45 (corpo), `high` 1,85. O site grafa `--font-lineheight-*` na tabela de tokens e `--font-line-height-*` nas tabelas de estilo (inconsistência do site; usar o nome que existir no CSS carregado).

| Elemento | Tamanho | Peso | Margens |
|---|---|---|---|
| H1 | `up-06` | light | mb `4x` |
| H2 | `up-05` | regular | mt `3xh`, mb `2xh` |
| H3 | `up-04` | medium | mt `3xh`, mb `2xh` |
| H4 | `up-03` | semi-bold | mt `3xh`, mb `2xh` |
| H5 | `up-02`, maiúsculas | bold | mt `3xh`, mb `2x` |
| H6 | `up-01`, maiúsculas | extra-bold | mt `3xh`, mb `2x` |
| Parágrafo | `up-01` | regular, linha `medium` | mb `2x` |
| Rótulo (label) | base | semi-bold | mb `half` |
| Input | `up-01` | medium | — |
| Placeholder | base, itálico | regular | — |
| Legend | `up-01` | semi-bold | mt/mb `2x` |
| Listas | `up-01` | regular | item mb `base`, lista mb `2x` |

Na grade de 4 colunas só mudam: H1 `up-04` medium; H2 `up-03` semi-bold; H3 `up-02` bold; H4 `up-01` bold; H5 base extra-bold; H6 `down-01`; parágrafo base.

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 20 | Tipografia | Rawline com fallback Raleway e `sans-serif` | S:fundamentos-visuais/tipografia | Computed font-family |
| 21 | Tipografia | Corpo na base 14px; nenhum tamanho fora da escala Minor Third; usar `em`/`rem` (exceto a base) para o usuário poder redimensionar | S:fundamentos-visuais/tipografia | Listar `font-size` |
| 22 | Tipografia | Pesos só os 9 oficiais (tokens `--font-weight-*`) | S:fundamentos-visuais/tipografia | Listar `font-weight` |
| 23 | Tipografia | Valor de destaque (KPI "Credenciamentos ativos") em tamanho da escala (`up-0x`) com peso semi-bold/bold; rótulo permanente (projeto) | S:fundamentos-visuais/tipografia; projeto | Conferir token |
| 24 | Tipografia | Entrelinha 1,45 até a fonte base, 1,15 acima; h1–h6 e `label`/`legend`/`mark`/`code` com os estilos da tabela acima; HTML semântico para o leitor de tela distinguir os elementos | S:fundamentos-visuais/tipografia | Computed line-height |
| 25 | Tipografia | Texto redimensionável até 200% sem perda de conteúdo (WCAG 1.4.4): o site pede `em`/`rem` mas não cita o critério | S:fundamentos-visuais/tipografia; [Pendente de confirmação] do critério | Zoom 200% |
| 26 | Tipografia | Números em tabela alinhados à direita, datas com a mesma quantidade de dígitos (01/01/89), milhar com ponto e decimal com vírgula (1.398.672,00) | S:padroes/writing/principios-writing; S:padroes/writing/microcopy | Conferir colunas numéricas |

### 2.3 Espaçamento (`S:fundamentos-visuais/espacamento`)

`box-sizing: border-box`. Padrão de qualquer elemento = 0px. **Layout (8px):** `--spacing-scale-base` 8 · `2x` 16 · `3x` 24 · `4x` 32 · `5x` 40 · `6x` 48 · `7x` 56 · `8x` 64 · `9x` 72 · `10x` 80. **Ajuste (4px), só para textos e ícones:** `half` 4 · `baseh` 12 · `2xh` 20 · `3xh` 28 · `4xh` 36 · `5xh` 44. Alinhamento padrão: `--spacing-vertical-top` e `--spacing-horizontal-left`.

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 30 | Espaçamento | Margens/paddings/gaps de **componentes** só com a escala Layout; escala Ajuste (4px) só para texto e ícone; sem valores avulsos | S:fundamentos-visuais/espacamento | Listar `margin/padding/gap` |
| 31 | Espaçamento | Área mínima de ação: ≥24px para mouse e ≥40px para toque (padrão Densidade); ícones interativos 24x24 (clique) e **48x48 (toque)**; se o elemento for menor, a área de ação ultrapassa-o | S:padroes/design/densidade; S:fundamentos-visuais/iconografia | Medir alvos no celular |
| 32 | Espaçamento | Margens vizinhas não se somam: iguais = vale uma; diferentes = vale a maior | S:fundamentos-visuais/espacamento | Revisão visual |
| 33 | Espaçamento | Evitar largura/altura absolutas em componentes com vários filhos; se fixar, definir máximos/mínimos | S:fundamentos-visuais/espacamento | Redimensionar |

### 2.4 Grid e breakpoints (`S:fundamentos-visuais/grid`)

| Faixa | Breakpoint | Grid | Gutter | Margem | max-width |
|---|---|---|---|---|---|
| Smartphone retrato | 0–575 | 4 col | 16px | 8px | até 559 (fluida = 100%) |
| Smartphone paisagem / tablet retrato | 576–991 | 8 col | 24px | 40px | 496–911 |
| Tablet paisagem | 992–1279 | 8 col | 24px | 40px | 912–1199 |
| Desktop | 1280–1599 | 12 col | 24px | 40px | 1200–1519 |
| TV | ≥1600 | 12 col | 40px | 40px | ≥1520 (fixa recomendada) |

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 40 | Grid | Layout nas grades 4/8/12 colunas por faixa; grid **fluida** recomendada para sistemas que aproveitam a tela; **fixa** para portais informativos; conteúdo informativo nunca ultrapassa as margens (só fundos podem "sangrar") | S:fundamentos-visuais/grid | Redimensionar em 360, 768, 1024, 1280, 1600 |
| 41 | Grid | Media queries nos breakpoints 576/992/1280/1600px | S:fundamentos-visuais/grid | Listar `@media` |
| 42 | Grid | Evitar rolagem horizontal da página; em tabela, rolagem horizontal é aceita (principalmente na grade de 4 col.), com barra de paginação e título fixos | S:padroes/design/contentoverflow; S:components/table | 320px de largura |
| 43 | Grid | Nomes das classes da grade (`row`, `col-*`): [Pendente de confirmação] (não estão nas páginas lidas); validar no pacote antes de usar | — | — |
| 44 | Grid | Interface independente do dispositivo: validar a cada breakpoint e tratar toque vs clique | S:fundamentos-visuais/grid | Teste por breakpoint |

### 2.5 Elevação, superfície e camadas (`S:fundamentos-visuais/elevacao`, `/superficie`)

Sombra = offset Y (varia com a camada) + blur `--surface-blur-lg` (6px) + cor `--pure-100` + opacidade `--surface-opacity-xs` (0,16). Camadas: **0** sem sombra (formulário, botão, texto, tabela, breadcrumb, lista, paginação, message, loading, abas, footer); **1** (`--surface-offset-sm`, 1px): header, menu fixo, **card**, magic button, e quem oculta conteúdo em rolagem; **2** (`md`, 3px): select, notification, datetimepicker; **3** (`lg`, 6px): menu/header/footer flutuante; **4** (`xl`, 9px): tooltip, modal, cookiebar, loading com scrim. Tokens `--z-index-layer-0…4`.
Bordas: `--surface-width-none/sm/md/lg` = 0/1/2/4px; estilo `solid`/`dashed`; cor padrão `--gray-40`. Cantos: `--surface-rounder-none/sm/md/lg/pill` = 0/4/8/16/altura÷2. Opacidade: `none` 0, `default` 1, `xs` .16, `sm` .30, `md` .45, `lg` .65, `xl` .85. Overlay de foco (scrim): `--pure-100` a `--surface-opacity-md`.

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 50 | Elevação | Sombras só pelos tokens de camada; elevação não é decoração e o elemento mais relevante fica na camada mais alta | S:fundamentos-visuais/elevacao | Listar `box-shadow` |
| 51 | Elevação | Raios só 4/8/16px ou pill; manter o mesmo raio em todos os componentes do produto | S:fundamentos-visuais/superficie | Listar `border-radius` |
| 52 | Elevação | Sobreposições usam a camada certa (select/notification=2, tooltip/modal=4) via `--z-index-layer-*`; itens da mesma camada não ocupam o mesmo espaço | S:fundamentos-visuais/elevacao | Listar `z-index` |
| 53 | Elevação | Cada tipo de componente na sua camada (tabela e breadcrumb na 0; card e header na 1) | S:fundamentos-visuais/elevacao | Conferir sombras |
| 54 | Elevação | Rolagem interna: as bordas que ocultam conteúdo sobem à camada 1 com sombra | S:fundamentos-visuais/elevacao; S:padroes/design/contentoverflow | Rolar uma lista |
| 55 | Superfície | Fundo padrão branco; o excesso de bordas gera ruído visual; borda quando o componente claro precisa se destacar | S:fundamentos-visuais/superficie | Revisão |
| 56 | Superfície | Overlay de legibilidade (texto sobre imagem/mapa) usa o degradê `--surface-overlay-text`; evitar overlay colorido que gere novo matiz | S:fundamentos-visuais/superficie | Mapa com rótulos |

### 2.6 Estados (`S:fundamentos-visuais/estados`)

Dois grupos: **interação** (interativo, desabilitado, hover, pressionado, foco, ativo, selecionado, arrastando, arrastar, dropzone, visitado, ligado/desligado) e **aviso** (erro, alerta, sucesso, informativo). Prioridade: estado por interação direta do usuário vence os herdados.

| Estado | Especificação do site |
|---|---|
| Interativo | cor `--blue-warm-vivid-70` (escuro `--blue-warm-20`); link com sublinhado, dispensável só se atrapalhar a leitura (evitar em listas/objetos/texto longo); link externo leva ícone de "container com seta" |
| Hover | overlay da cor do primeiro plano: opacidade `--surface-opacity-xs` (claro) / `sm` (escuro); cursor `pointer`; um só por vez; desabilitado não recebe hover |
| Pressionado | overlay `md` (claro) / `lg` (escuro); sombra removida |
| Foco | borda `--surface-width-lg` **tracejada** (`dashed`), padding de segurança `--spacing-scale-half`; cor `--gold-vivid-40` (claro) / `--gold-vivid-20` (escuro); **foco tênue**: borda `--surface-width-md` sólida (inputs/foco indireto) |
| Ativo | alto destaque no conjunto; só **um** por conjunto; cor `--blue-warm-vivid-80` + borda `--surface-width-lg`; não usar onde vários itens ficam ativos (listas com expansão) |
| Selecionado | fundo `--blue-warm-vivid-50` + elemento gráfico (`fa-check`, checkbox ou radio); **não** em navegação (menu/abas), modal, tooltip, button, message, divider |
| Desabilitado | opacidade `--surface-opacity-md`, cursor `not-allowed`, sem sombra; sem contraste obrigatório; **evitar**: se não puder ser habilitado, não exibir (navegação, modal, tooltip, magic button) |
| Visitado | `--blue-warm-vivid-80` (claro) / `--gray-20` (escuro); usar com cuidado por privacidade |
| Erro / Alerta / Sucesso / Informativo | `--red-vivid-50` + `times-circle` / `--yellow-vivid-20` + `exclamation-triangle` / `--green-cool-vivid-50` + `check-circle` / `--blue-warm-vivid-60` + `info-circle`; erro persiste até resolver; sucesso/informativo podem sumir sozinhos |

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 60 | Estados | Todo elemento interativo com foco visível nos moldes acima; **clicar/tocar não deve gerar foco visual por padrão**, mas teclado/voz sim; sem `outline: none` sem substituto; só um elemento com foco por vez; desabilitado não recebe foco | S:fundamentos-visuais/estados | Navegar por Tab |
| 61 | Estados | Hover e pressionado por overlay com os tokens de opacidade acima, sem transições elaboradas | S:fundamentos-visuais/estados | Mouse |
| 62 | Estados | Desabilitado: `disabled` + opacidade `md` + cursor; preferir ocultar o que o usuário não pode habilitar | S:fundamentos-visuais/estados | Fora da ordem de Tab |
| 63 | Estados | Ativo (um por conjunto, ex.: aba atual) distinto de selecionado (vários, ação posterior, ex.: linhas marcadas); marcar também com ARIA (reforço do projeto; ARIA por componente: [Pendente de confirmação] no site) | S:fundamentos-visuais/estados | Inspecionar |
| 64 | Estados | Estados de aviso = cor + ícone + texto; tag de status sem rótulo informa só por cor (o DS recomenda tooltip; **o projeto exige texto**) | S:fundamentos-visuais/estados; S:components/tag; projeto | Escala de cinza |
| 65 | Estados | Ao combinar estados, exibir todos; contraste do estado conferido também em fundo escuro, invertendo cores se preciso | S:fundamentos-visuais/estados | Revisão |

### 2.7 Iconografia (`S:fundamentos-visuais/iconografia`)

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 70 | Iconografia | Font Awesome **5.10.2** (a página) ou 5.11.2 (CDN do exemplo), estilos `fas` e `fab`; tamanho base 16px (`--icon-size-base`); escala `fa-xs/sm/lg/2x…10x` mapeada para `--icon-size-*` | S:fundamentos-visuais/iconografia | Conferir classes |
| 71 | Iconografia | Ícone **decorativo**: `aria-hidden`. Ícone **semântico** sem texto: `aria-hidden` no ícone + texto alternativo em `.sr-only`, ou `aria-label` no elemento interativo; `title` só como dica de mouse | S:fundamentos-visuais/iconografia | Buscar `<i ` sem `aria-hidden` |
| 72 | Iconografia | Usar o ícone com o **significado oficial** (ex.: Pesquisar `fa-search`, Filtrar `fa-sliders-h`, Baixar `fa-download`, Fechar `fa-times`, Atualizar `fa-sync`, Voltar `fa-chevron-left`, Expandir `fa-chevron-down`, Retrair `fa-chevron-up`, Ajuda `fa-question`, Sucesso `fa-check-circle`, Informativa `fa-info-circle`, Alerta `fa-exclamation-triangle`, Erro `fa-times-circle`, Ordenar `fa-sort`/`-up`/`-down`, Imprimir `fa-print`, Data `fa-calendar-alt`); rótulo ou hint ao lado reforça o sentido | S:fundamentos-visuais/iconografia | Comparar com a tabela do site |
| 73 | Iconografia | Ícone de ação representa a ação executada; ícone novo só se o DS não tiver, no mesmo padrão sólido | S:fundamentos-visuais/iconografia | Revisão |

### 2.8 Movimento (`S:fundamentos-visuais/movimento`)

Easing: `--animation-ease` `cubic-bezier(.25,.1,.25,1)`, `-ease-in` `(.42,0,1,1)`, `-ease-out` `(0,0,.58,1)`, `-ease-in-out` `(.42,0,.58,1)`, `-ease-linear`. Durações: `--duration-very-fast` 0,10s · `fast` 0,30s · `moderate` 0,50s · `slow` 0,80s · `very-slow` 1s. Ease-out é geralmente o melhor para interface.

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 80 | Movimento | Animar só com função (feedback, relação entre elementos); sutil, curta e com os tokens de easing/duração; micro 0,10–0,49s; feedback simples (checkbox/switch) ≈0,10s; entrada um pouco mais longa que saída | S:fundamentos-visuais/movimento | Listar `transition`/`animation` |
| 81 | Movimento | Dar **controle ao usuário** (ativar/desativar) e **pausa** para movimento que inicie sozinho e dure >5s, ou que pisque >3s (risco de convulsão) | S:fundamentos-visuais/movimento | Badge "Agora" que pisca |
| 82 | Movimento | Animação **nunca** é a única fonte de informação; oferecer alternativa estática | S:fundamentos-visuais/movimento | Desligar animações: informação continua |
| 83 | Movimento | Não animar vários elementos ao mesmo tempo; evitar movimentos abruptos; priorizar componentes nativos | S:fundamentos-visuais/movimento | Revisão |
| 84 | Movimento | `prefers-reduced-motion`: o site gov.br não cita a media query; o projeto a exige. A doc do WBC a adota (transições só em `no-preference`; `reduce` = sem transição, loading estático, sem autoplay) | projeto; W:movimento-e-elevacao-webcomponents (seção 7) | Ativar redução no SO |

## 3. Componentes

Notação: **site** = comportamento/uso lido em `S:components/<nome>`; **marcação** = classes HTML do `npm:ex:<nome>` (o site raspado não traz a aba "Códigos").

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 100 | Cabeçalho | Só o **título** é obrigatório (identifica site/sistema); logo, assinatura, subtítulo, menu e busca são opcionais; título em até **2 linhas** (excedente truncado com "…") e altura acompanha | S:components/header | Inspecionar |
| 101 | Cabeçalho | Logo: marca **SUS** do projeto; **proibido gov.br**. Logo limitada pela altura, largura máx. 4x a altura; tamanho large (maior ênfase), medium (mobile 4 col.) ou small (compacto/sticky); usar separador entre logo e assinatura; assinatura oculta no mobile e no sticky | S:components/header; projeto | Buscar "gov.br" nas imagens; conferir `sus-logo.png` |
| 102 | Cabeçalho | Marcação: `header.br-header` com `.header-top` e `.header-bottom`; botões só ícone com `aria-label`; gatilhos de dropdown com `aria-expanded` (o exemplo oficial não expõe) | npm:ex:header | Leitor de tela |
| 103 | Cabeçalho | Sem `href="javascript:void(0)"`; usar URL real ou `<button>` | npm:ex:header | Buscar `javascript:` |
| 104 | Skiplink | Usar em **todas as páginas**; composto com **até 4 itens** (senão tipo simples, um por vez com notação (1/4)); tag opcional com o número da tecla; marcação `nav.br-skiplink` com âncoras `#main-content`, menu, busca, `#footer` e `accesskey` 1–4 | S:components/skiplink; npm:ex:skiplink | Tab inicial; âncoras existem |
| 105 | Breadcrumb | Usar **se houver hierarquia** (D10); botão Home sempre presente (exceto no tipo especial); página atual em semi-bold, sem link; só ícones separadores não são interativos; tipo especial no mobile (4 col.); truncar com menu quando >5 itens (nunca Home, último ancestral e título atual); escolha padrão/especial não pode ser revertida no produto. Marcação: `nav.br-breadcrumb[aria-label]` > `ol.crumb-list` > `li.crumb` | S:components/breadcrumb; npm:ex:breadcrumb | Inspecionar |
| 106 | Breadcrumb | Item atual `span[aria-current="page"]` em `li[data-active="active"]` | npm:ex:breadcrumb | Inspecionar |
| 107 | Card | Superfície branca com sombra (camada 1) e 3 áreas: **títulos** (título, subtítulo, ícone, avatar, tag), **conteúdo** (qualquer componente **exceto** navegação: carrossel, paginação, abas, menu) e **ações** (só botões e links); altura fixa ou flexível; área de respiro entre cards. Marcação `.br-card` > `.card-header/.card-content/.card-footer` | S:components/card; npm:ex:card | Inspecionar |
| 108 | Card | KPI "Credenciamentos ativos": rótulo permanente junto do valor (projeto), valor em tamanho da escala, botões só ícone com `aria-label` | projeto; S:components/card | Inspecionar |
| 109 | Card | Expansão: botão `data-toggle="collapse"` com `aria-controls`, `aria-expanded` e `hidden` no painel | npm:ex:card | Alternar |
| 110 | Tabela | Cabeçalho de coluna **obrigatório** e conciso (menor que os dados); título opcional em 1 linha; ≤4 ícones de ação no título, mais que isso em menu `ellipsis-v`. Marcação `.br-table` com `<caption>`, `th scope="col"`, `td data-th` | S:components/table; npm:ex:table | Inspecionar |
| 111 | Tabela | Linha expansível: botão **chevron-down no início da linha** que vira chevron-up; evitar tabela/card dentro do conteúdo expandido (preferir lista); marcação `.br-button.circle.small[data-toggle="collapse"][data-target]` e `tr.collapse[hidden]` com `colspan` correto e IDs únicos. Em tabela gerada por JS, instanciar o collapse depois de montar as linhas (inferência da regra 4) | S:components/table; S:utilitarios/js/collapse; npm:ex:table | Expandir; `colspan` |
| 112 | Tabela | Botão de expandir com `aria-label` único por linha e `aria-expanded` | npm:ex:table | Leitor de tela |
| 113 | Tabela | Densidades alta/padrão/baixa; hover de linha só em tabela extensa **sem** interatividade; texto longo truncado com reticências e tooltip (ver 200); grade de 4 col.: rolagem horizontal, com busca e paginação | S:components/table | — |
| 114 | Tabela | Legenda do farol fora das células, com ícone + texto | projeto; S:components/tag | Inspecionar |
| 115 | Tabela | Paginação dentro da tabela é a **contextual** (ver 162); **sem dados**, o estado vazio substitui toda a tabela (cabeçalho, linhas, rodapé) — ver 306 | S:components/table; S:padroes/design/emptystates | Filtro sem resultado |
| 116 | Tabela | Seleção de linhas (checkbox no início, indeterminado no cabeçalho) e barra contextual só se houver ação sobre linhas; seleção usa o estado "selecionado" (ver 2.6) | S:components/table | — |
| 120 | Tag | Superfície pill/retangular, 1 cor por tag, rótulo curto (de preferência 1 palavra) e **substantivo/adjetivo, nunca verbo**; altura fixa, uma linha; ícone à esquerda do rótulo; tag de texto nunca é interativa. Marcação `span.br-tag` + `bg-*` | S:components/tag; npm:ex:tag | Inspecionar |
| 121 | Tag | **Tag de status** (círculo) pode ser só cor, e o site recomenda tooltip sem rótulo; para o farol do painel usar rótulo visível + ícone, nunca só o ponto (regra do projeto, mais estrita que o DS) | S:components/tag; projeto | Escala de cinza |
| 122 | Tag | Só tags de status, ícone e contagem têm borda; contagem até 3 dígitos (999+) com tooltip da quantidade exata; interativas: dispensável ou persistente, nunca as duas no mesmo grupo; densidades small/medium/large | S:components/tag | — |
| 123 | Tag | Contagem: número `aria-hidden` + `title` descritivo (marcação) | npm:ex:tag | — |
| 124 | Tag | Muitas cores desviam a atenção: manter poucas e coerentes com o farol | S:components/tag | Revisão |
| 130 | Message | Superfície com cor semântica + ícone à esquerda; **mensagem obrigatória**, título opcional; botão fechar no topo direito (terciário, alta densidade); tipo **padrão** (global, entre header e breadcrumb, ou direcionado, perto do elemento) e **contextual** (sem botão fechar). Marcação `.br-message.{success,info,warning,danger}` com `.content[role="alert"]` | S:components/message; npm:ex:message | Inspecionar |
| 131 | Message | Ícones: sucesso `check-circle`, alerta `exclamation-triangle`, erro `times-circle`, informação `info-circle`, fechar `times` | S:components/message | — |
| 132 | Message | "Nenhum resultado": preferir o **estado vazio** (ver 306) ou message informativa com título e orientação; `role="alert"` vs `role="status"` em carga sem resultado: aba "Acessibilidade" não capturada | S:padroes/design/emptystates; [Pendente de confirmação] do papel ARIA | — |
| 133 | Feedback | Mensagem contextual de campo: marcação `span.feedback.*[role="alert"]` ligada ao campo por `aria-describedby` | npm:ex:input | — |
| 134 | Message | Mensagens curtas; erro diz o que houve, por quê e como resolver; não culpar o usuário; sem "Ops!"; sucesso pode usar "com sucesso!" | S:padroes/writing/microcopy | Revisar textos |
| 140 | Tabs | Para organizar conteúdo em seções; **1 a 3 palavras** por rótulo, só 1 item ativo; superfície se estende até a borda e tem divider; **overflow**: swipe com sombras nas laterais e setas esquerda/direita do teclado; só ícone exige tooltip; contador opcional; 3 densidades. Marcação `.br-tab` > `nav.tab-nav` > `button[data-panel]`, painéis `.tab-panel` | S:components/tab; npm:ex:tab | Inspecionar |
| 141 | Tabs | ARIA de abas (`role="tablist/tab/tabpanel"`, `aria-selected`, `aria-controls`) e teclado: o exemplo npm não traz; o site cita a prática "Tabs with Manual Activation" (W3C APG) só para o swipe por teclado; ARIA obrigatório do DS: [Pendente de confirmação] | S:components/tab; npm:ex:tab | Teclado |
| 142 | Tabs | **Não aninhar abas**: para subdividir (Ativos/Encerrados dentro de uma aba) usar `List` ou nivelar no mesmo componente (D6) | S:components/tab | Revisar hierarquia |
| 143 | Tabs | Contador na aba: `data-counter="true"` (marcação do pacote) | npm:src:tab | — |
| 150 | Select | `select` (único) e `multiselect`; marcação `.br-select` > `.br-input` + botão `data-trigger` + `.br-list`/`.br-item` com radio ou checkbox; lista flutua na camada 2 com sombra; ordenar itens de forma lógica (alfabética, cronológica); multiselect com "Selecionar Todos/Desselecionar Todos" e estado intermediário; lista com rolagem se longa | S:components/select; npm:ex:select | Inspecionar |
| 151 | Select | Rótulo sempre visível (nunca só placeholder); mesmo posicionamento do rótulo em todos os campos da página; opções curtas | S:components/select; S:padroes/design/formulario | — |
| 152 | Input | Tipos padrão e **destaque** (busca); rótulo acima do campo (padrão) ou à esquerda, igual em toda a página; campo com tamanho próximo ao da entrada esperada; ícone ilustrativo à esquerda; ação interna só botão circular terciário de alta densidade; alturas: destaque `7x` (56px), baixa `6x`, média `5x` | S:components/input | Inspecionar |
| 153 | Input | Rótulo oculto só com `.sr-only` se houver equivalente visível (marcação `input-highlight`); todo campo precisa de rótulo; placeholder não substitui rótulo; placeholder em itálico, só para dica/formato | S:padroes/design/formulario; npm:ex:input | — |
| 154 | Autocomplete | O DS descreve **autocomplete como recurso** do input (sugestões em lista dropdown, **ordem alfabética**, atualização instantânea, **loading** se demorar, **feedback no lugar da lista quando não há sugestão**) e o filtro digitável do select; **não há componente nem especificação ARIA/teclado** nos textos lidos; usar a marcação de `br-input` + `br-list`/`br-item` e validar setas, Esc, Enter, `aria-activedescendant`/`combobox` manualmente [Pendente de confirmação] | S:components/input; S:components/select | Teclado e leitor de tela |
| 155 | Input | Máscara funciona ao digitar e ao colar; não dividir um dado em vários campos; texto auxiliar compacto; sem "Redefinir/Limpar formulário" como botão de formulário | S:components/input; S:padroes/design/formulario | — |
| 160 | Paginação | Obrigatórios: setas e identificadores numéricos; opcionais: reticências (menu com rolagem a partir de >4 itens), módulo de exibição ("Exibir N"), informação e atalho (select de páginas). Marcação `nav.br-pagination[data-total][data-current]` | S:components/pagination; npm:ex:pagination | Inspecionar |
| 161 | Paginação | Página atual também com `aria-current="page"` (o exemplo oficial não traz) | npm:ex:pagination; [Pendente de confirmação] no site | — |
| 162 | Paginação | Tipo **contextual** (dentro de tabela/card): só setas obrigatórias; **padrão** centralizada no fim do conteúdo; na grade de 4 col. usar densidade baixa (toque) e ocultar módulos menos relevantes | S:components/pagination | — |
| 170 | Accordion | Componente **obsoleto**: usar Utilitário JS > Accordion e o padrão Collapse (D8); evitar acordeão dentro de acordeão; `single`/`negative` da marcação npm | S:components/accordion; S:utilitarios/js/accordion; npm:ex:accordion | Alternar |
| 171 | Collapse | **Acionador**, **identificador** (chevron) e **conteúdo expansível**; chevron-down abre para baixo, chevron-up recolhe; identificador à direita, **à esquerda em linha de tabela e no header**; expandir empurra o conteúdo seguinte; vários itens abertos ao mesmo tempo ou um por vez conforme o caso; não usar para conteúdo prioritário, muito curto ou muito extenso. JS: `new core.Collapse({trigger, iconToShow:'fa-chevron-down', iconToHide:'fa-chevron-up', useIcons:true}).setBehavior()` com `data-toggle="collapse"` e alvo por `id` | S:padroes/design/collapse; S:utilitarios/js/collapse | Alternar; `aria-expanded` |
| 172 | Collapse | A área de clique é todo o acionador, ou só o botão (então o ícone fica no botão); estrutura hierárquica: indentação ≥16px (2x), no máximo 2–3 níveis | S:padroes/design/collapse | — |
| 180 | Button | Padrão (rótulo) ou **circular** (só ícone, ícone obrigatório); 3 ênfases (primária, secundária, terciária); rótulo de 1–2 palavras (nunca 3+ nem quebra de linha), verbo no infinitivo; botões em Title Case ("Gerar Relatório"); comportamento bloco recomendado em toque. Marcação `.br-button` + `primary/secondary`, `circle`, `block`, `small/large`, `type="button"` | S:components/button; S:padroes/writing/microcopy; npm:ex:button | Inspecionar |
| 181 | Button | Ênfase primária só em ações estratégicas (uma por região); em formulários primária à direita, secundária ao meio, terciária à esquerda; botão só ícone com `aria-label` descritivo | S:components/button; S:padroes/design/formulario | — |
| 182 | Button | Modo escuro: `dark-mode` sobre fundo escuro (marcação npm) | npm:ex:button | — |
| 190 | Step | Só para fluxo sequencial com progressão (etapas concluídas, em andamento, não concluídas); sem relação sequencial usar menu ou abas | S:components/step | — |
| 191 | Step | Marcação: `nav.br-step`, `.step-progress[role="listbox"]`, `button[role="option"][aria-posinset][aria-setsize]`; estados `data-alert` com texto além da cor | npm:ex:step | — |
| 200 | Tooltip | Componente **obsoleto** em 3.7.0 (D7). Se for usado: `.br-tooltip[role="tooltip"]` na camada 4 com sombra, texto curto e simples, sem só título; **em toque só abre por clique ou foco**; informação essencial (prazo, farol) **nunca só em tooltip**. Decisão do projeto: manter, trocar por `title`/texto visível/popover acessível ou aguardar o substituto [Pendente de confirmação] | S:components/tooltip; S:padroes/design/contentoverflow; projeto | Tab abre/fecha |
| 201 | Tooltip | Texto truncado deve oferecer o texto completo (tooltip é o recurso indicado pelo site; parágrafo truncado dispensa) | S:padroes/design/contentoverflow | — |
| 202 | Loading | Indeterminado (com rótulo opcional) ou determinado (0–100%, cancelar opcional); **scrim + superfície obrigatórios** se bloquear a tela, senão não usar superfície; camada 4 com scrim; informar sempre que o usuário espera o servidor. Marcação `.br-loading[role="progressbar"][aria-label]` | S:components/loading; S:padroes/design/formulario; npm:ex:loading | Carga da API |
| 203 | Lista/Divider | `.br-list`/`.br-item`/`.br-divider`; divider separa seções quando o espaço em branco não basta, horizontal ou vertical | S:components/list; S:components/divider | — |
| 204 | Footer | Container obrigatório; layout consistente e previsível em todas as páginas; logo, mapa do site e informação legal opcionais | S:components/footer | — |
| 205 | Modal | Para interromper o fluxo (erro bloqueante, decisão crítica, entrada de dados sem perder contexto); superfície branca, título claro (evitar "Aviso!", "Você tem certeza?"), conteúdo conciso com rolagem vertical interna, **overlay** que torna a página inacessível, botões obrigatórios; não abusar | S:components/modal; S:padroes/design/contentoverflow | — |
| 206 | Notification | Lista cronológica de eventos relevantes (item e divider obrigatórios); camada 2 | S:components/notification; S:fundamentos-visuais/elevacao | — |

Componentes disponíveis em 3.7.0 (36): accordion*, avatar, breadcrumb, button, card, carousel, checkbox, cookiebar, datetimepicker, divider, footer, header, input, item, list, loading, magicbutton, menu, message, modal, notification, pagination, radio, scrim*, select, signin, skiplink, step, switch, tab, table, tag, textarea, tooltip*, upload, wizard (*obsoletos).

## 4. Padrões de página

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 300 | Padrões | Ordem sugerida: skiplink > header > (breadcrumb, **se houver hierarquia**) > `main#main-content` > footer; um `h1` por página e níveis h1–h6 em hierarquia sem pular | S:components/skiplink; S:guias/acessibilidade-html | Landmarks e headings |
| 301 | Padrões | Mensagem global entre header e breadcrumb; ajuda sempre acessível no cabeçalho, rodapé ou menu | S:components/message; S:padroes/design/ajuda-comunicacao | — |
| 302 | Padrões | Marca: sem logo gov.br; identidade SUS do projeto; não imitar assinatura/selo gov.br | projeto | Buscar "gov.br" em imagens e textos do cabeçalho |
| 303 | Padrões | Regras de negócio (U/V, competências, dia 15, KPI único) não mudam por ajuste visual | projeto | Revisão |
| 304 | Formulário/filtros | Rótulos visíveis e acima do campo; **todos** os campos têm rótulo; opcionais marcados com "(opcional)" (ou só obrigatórios, nunca os dois); uma coluna (exceto campos complementares como Estado/Cidade); ordem lógica e previsível dos filtros; agrupar com `fieldset`+`legend` (sem borda visual, `margin-bottom` `5x`); o primeiro campo obrigatório recebe foco; teclado e leitor de tela em todos os campos; texto de ajuda curto, ícone de ajuda só para info não essencial | S:padroes/design/formulario | Revisar a área de filtros |
| 305 | Navegação | Abas = navegação lateral entre irmãos; manter visível o caminho para o pai; busca = navegação direta (manter caminho de volta); paginação = navegação sequencial; ao voltar, restaurar estado e rolagem; mensagem clara se a tela anterior não existir | S:padroes/design/navegacao | Fluxo município > geral |
| 306 | Estado vazio | Anatomia: imagem opcional, **título** (positivo) e **corpo** obrigatórios, botão/link opcional logo abaixo. Para "sem resultado de busca": explicar e oferecer **"Limpar Filtros"**; erro de dados diz o que houve e como agir; texto contextual; imagem decorativa com `alt=""`; no lugar da tabela substitui a estrutura toda; erros HTTP seguem o Template de Erro | S:padroes/design/emptystates; S:templates/erro | Filtro sem resultado e falha da API |
| 307 | Densidade | Escala de 4px em 4px; alta densidade em espaços restritos (botão na linha da tabela), **nunca** em elementos de interação frequente ou que pedem destaque; mínimo 4px entre conteúdo e borda; manter a mesma densidade em componentes vizinhos | S:padroes/design/densidade | Revisão |
| 308 | Excesso de conteúdo | Essencial sempre visível; rolagem vertical (evitar horizontal na página); complementar em collapse/dropdown/tooltip ("Mostrar mais/menos"); truncamento só em textos estáticos, links, breadcrumb, paginação, itens de tabela (**nunca** em título, menu, rótulo de botão, mensagem de erro/validação, notificação), com reticências e três ou mais caracteres visíveis; link externo sinalizado com ícone | S:padroes/design/contentoverflow | Tabelas largas, nomes longos |
| 309 | Ajuda | Ajuda contextual perto do elemento com ícone `question`/`question-circle` (ou `info`); texto curto; mais detalhe em modal; acesso imediato, sem menus intermediários | S:padroes/design/ajuda-comunicacao | — |
| 310 | Escrita | Linguagem simples, voz neutra e direta, sem jargão; mesma palavra para o mesmo conceito; títulos e subtítulos em maiúscula nas palavras principais, sem ponto final; rótulos de entrada só a 1ª letra maiúscula; evitar siglas sem explicar (as do projeto só com significado confirmado); links com destino claro (sem "clique aqui") | S:padroes/writing/principios-writing; S:padroes/writing/microcopy | Revisar textos |
| 311 | Gráfico | Título, subtítulo, descrição textual e **fonte** na área de rodapé; barras horizontais quando rótulos são longos; mostrar o valor quando possível; descrição acessível do gráfico/mapa (tooltip do mapa não basta) | S:padroes/design/grafico | Dashboard (Chart.js, Leaflet) |

## 5. Acessibilidade

O site adota **WCAG 2.1 (A, AA, AAA)** e o **eMAG**; o DS trabalha no mínimo AA. Validadores automáticos não bastam: é preciso validação manual (`S:acessibilidade`; `S:guias/acessibilidade-html`).

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 400 | A11y | Ícone decorativo com `aria-hidden`; ícone/botão sem texto com `aria-label` ou `.sr-only`; imagem informativa com `alt`; imagem decorativa `alt=""` (evitar `role="presentation"`) | S:fundamentos-visuais/iconografia; S:padroes/design/emptystates | Buscar `<i ` e `<img` sem atributo |
| 401 | A11y | HTML válido e **semântico**; `label[for]` em todo campo; IDs únicos (os exemplos oficiais repetem IDs); listas e tabelas só para sua finalidade (nunca tabela para diagramar); `lang="pt-BR"` e `lang` em trechos de outro idioma | S:guias/acessibilidade-html; S:guias/boas-praticas-de-html | Validador HTML |
| 402 | A11y | Foco visível (ver 60) e ordem de tabulação lógica; nada preso | S:fundamentos-visuais/estados; S:acessibilidade | Navegar só por teclado |
| 403 | A11y | Toda função operável por teclado (WCAG diretriz 2.1): abas, expansão de linhas, autocomplete, select, paginação | S:acessibilidade | Teclado |
| 404 | A11y | Mensagens dinâmicas anunciadas (`role="alert"` em message e feedback); papel ARIA exato por componente: [Pendente de confirmação] | npm:ex:message | Leitor de tela |
| 405 | A11y | Farol: cor + texto + ícone (projeto); coerente com o estado "aviso" do DS | S:fundamentos-visuais/estados; projeto | Escala de cinza |
| 406 | A11y | Movimento: ver 80–84; pausa para movimento >5s; sem pisca >3s; o pulso do prazo só quando há ação (projeto) | S:fundamentos-visuais/movimento; projeto | Redução de movimento |
| 407 | A11y | Contraste AA (ver 15); estados desabilitados são exceção | S:fundamentos-visuais/cores; S:fundamentos-visuais/estados | Ferramenta de contraste |
| 408 | A11y | Testar com leitor de tela (NVDA); declarar o que **não** foi testado | projeto; S:acessibilidade | Relatório |
| 409 | A11y | `alt` da marca SUS identifica o órgão | S:guias/acessibilidade-html; projeto | — |
| 410 | A11y | WAI-ARIA: usar `role` e `aria-*` quando o HTML nativo não tiver a semântica; `states` mudam durante o ciclo do elemento (ex.: `aria-expanded`, `aria-checked`) e o JS os atualiza; o atributo `role` prevalece sobre o papel implícito, mas o estado nativo prevalece sobre o `aria-*` equivalente | S:guias/wai-aria | Inspecionar widgets customizados |
| 411 | A11y | Informar quando o usuário precisa esperar (loading/message); campo de entrada com orientação de formato; elevação implícita: leitor prioriza camadas altas (modal) | S:padroes/design/formulario; S:fundamentos-visuais/elevacao | — |
| 412 | A11y | Alvos de toque 48x48px para ícones e ≥40px em elementos interativos (ver 31) | S:fundamentos-visuais/iconografia; S:padroes/design/densidade | Celular |

## 6. Boas práticas de CSS (para o `<style>` do `index.html`)

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 500 | CSS | O DS usa normalize.css customizado como reset e arquitetura Atomic Design; comentar a finalidade de cada bloco de CSS e usar variáveis em vez de valores repetidos | S:guias/boas-praticas-de-css | Revisão do CSS |
| 501 | CSS | Tokens do DS continuam disponíveis para CSS próprio mesmo com `core-lite` | S:como-comecar/fluxo-dev | — |

## 7. Web Components (`@govbr-ds/webcomponents`, série 2.x) × CSS core 3.7.0

**Origem dos fatos.** Site gov.br: `S:como-comecar/fluxo-dev`, `S:home`. Repositório oficial `gitlab.com/govbr-ds/bibliotecas/wbc/govbr-ds-wbc`, pasta `apps/site/docs` (128 arquivos lidos em 20/11/2026; abreviação `W:<arquivo>` = `apps/site/docs/<arquivo>.mdx`): `guias-tecnicos/carregamento-e-csp`, `guias-essenciais/cdn`, `compatibilidade`, `customizacao/temas|css-parts|slots`, `guias-essenciais/acessibilidade|eventos|ciclo-de-vida|contrato-plataforma-e-apg|convencoes-de-api|navegacao-dos-componentes`, `guias-tecnicos/dados-remotos|content-visibility|receitas|movimento-e-elevacao-webcomponents`, `frameworks/javascript|formularios`, `versions.md`, `security`, `components/*`. A versão **2.7.0** vem do dado informado pelo usuário (npm `latest`, publicada em 10/11/2026); **nenhum arquivo lido cita o número 2.7.0** (`W:versions` só descreve a política semver e os canais `next/alpha/beta`).

**Limite da leitura.** Cada página `components/<nome>/index.mdx` é só um esqueleto que importa blocos de `stencil-generated-docs/` (visão geral, props, slots, eventos, parts, "Acessibilidade", "Interação por teclado", "Dados remotos", "Rolagem virtual e cabeçalho fixo", "Performance" etc.). Esses blocos são gerados no build e **não existem no repositório** (tentativa de baixar pelo raw do GitLab: HTTP 404); o `README` do pacote também é importado e não foi lido. Portanto: props, eventos, partes e teclado **por componente** não foram verificados; só os guias transversais.

### 7.1 O que o WBC é (fatos documentados)

- Custom Elements `br-*` com **Shadow DOM**, construídos com **Stencil** (`W:ciclo-de-vida`, `W:anatomia-de-componente`). Wrappers oficiais para Angular, React e Vue; **HTML/JavaScript sem framework é a "integração base"** (`W:frameworks/javascript`).
- Pacotes: `@govbr-ds/core` (estilos, tokens, utilitários globais) e `@govbr-ds/webcomponents` (elementos `br-*`). O core continua **necessário** para estilos globais e fontes; o npm instala como peers `@govbr-ds/core`, `@floating-ui/dom`, `date-fns` e `iconify-icon` (`W:dados-remotos`). Em CDN, só se citam core, WBC, Rawline/Raleway e Font Awesome 5.15.4 (`W:cdn`); como `iconify-icon` entra no uso via CDN: [Pendente de confirmação].
- A versão 1.x está só em manutenção; recomenda-se a 2.x (`W:introducao`).

### 7.2 Respostas às perguntas

**(1) Componentes.** Pelas pastas em `components/`: avatar, breadcrumb (+ breadcrumb-item, crumb), button, card, carousel (+ carousel-page), checkbox, checkbox-group, checkgroup, collapse, cookiebar (+ 6 subcomponentes), datetime-picker (+ date-picker, datetime-input, time-picker), divider, dropdown, footer (+ category, item, legal, logo, social), header (+ function, link, list, logo), icon, input, item, link, list, loading, magic-button, menu (+ 8 subcomponentes), message, modal, notification (+ body, header, item), pagination, radio, radio-group, scrim, select (+ select-input, select-list, select-option), sign-in, skip-link (+ skip-link-item), slider, step (+ step-item), switch, tab (+ tab-item), table (+ action, bar, body, cell, footer, header-cell, header, row), tag, textarea, tooltip, upload, wizard (+ wizard-panel). **Todos os que foram perguntados existem:** tabela (`br-table`), select com busca (`br-select` com `filterable` e `search-mode="remote"`), tabs (`br-tab`/`br-tab-item`), list (`br-list`), tag (`br-tag`, inclusive selecionável com `interaction-select`), message (`br-message`), collapse (`br-collapse`), pagination (`br-pagination`), header (`br-header`), breadcrumb (`br-breadcrumb`), modal (`br-modal`), loading (`br-loading`). **Não há pasta de accordion** (no core 3.7.0 ele é obsoleto, D8). Tooltip existe; se está obsoleto no WBC: [Pendente de confirmação]. "Autocomplete": `W:dados-remotos` e `W:content-visibility` descrevem `br-select` com `filterable`, busca remota, `setOptions()` e janela virtual interna; **não há componente "autocomplete" separado** nos arquivos lidos.

**(2) Tema e estilo** (`W:temas`, `W:css-parts`, `W:slots`, `W:convencoes-de-api`).
- **Variáveis CSS herdam pelo Shadow DOM:** o `core.min.css` continua definindo cores, tipografia e espaçamento do DS.
- **Variáveis por componente** no formato `--br-{componente}-{elemento}-{propriedade}`: por exemplo `--br-button-radius`, `--br-button-primary-background`, `--br-card-background`, `--br-card-padding`, `--br-input-background`, `--br-table-cell-background`, `--br-table-header-background`; as demais estão na seção "Variáveis CSS" de cada página (não lida).
- **Tema por tokens `--br-theme-*`** (superfície, texto, interativo, borda, foco, hover/pressionado, selecionado, alturas de controle, escala de espaço e tipografia, raios, sombras) com `data-theme="light|dark"`, via `@govbr-ds/webcomponents/dist/tokens.css`. **A página marca como beta:** "nomes dos tokens, valores padrão e comportamento podem mudar em versões futuras". Componentes de status (message) mantêm as cores de status do core.
- **CSS Parts** (`::part()`) só para partes documentadas (ex.: `br-button::part(button)`, `br-table::part(container)`, `br-table-cell::part(content)`); não atravessam camadas sem `exportparts`. **Slots** recebem conteúdo do Light DOM, que **continua recebendo o CSS global da página** (Font Awesome, classes próprias).
- **Dá para aplicar o visual do painel?** A base visual coincide (o painel já usa tokens do DS) e ajustes finos (raio, cor primária, padding do card, cabeçalho da tabela) são possíveis por variáveis e parts. **Pelo que está documentado, não dá** para estilizar por CSS externo o interior dos componentes: farol com texto+ícone, linha do tempo CNES/parcelas, badge "Agora" e KPI são conteúdo do painel e ficariam em slots ou fora dos componentes. Cobertura real de parts/variáveis por componente: [Pendente de confirmação].

**(3) CSP e inline** (`W:carregamento-e-csp`). O guia diz só: autorizar a origem do script em `script-src` ao usar CDN; autorizar fontes e estilos do core nas diretivas correspondentes; "prefira arquivos de script a código inline"; validar com a política real ("o Playground não substitui essa verificação"). **Não diz** se o runtime Stencil injeta `<style>`/constructable stylesheets, se exige `unsafe-inline`, nonce ou SRI. Como o painel é um único `index.html` com `<script>` e `<style>` inline, a lacuna importa. Cabeçalhos CSP atuais do painel no Render: [Pendente de confirmação]. SRI não é documentado (regra 6 segue pendente).

**(4) Desempenho e listas grandes.** O runtime "carrega os chunks dos componentes utilizados sob demanda" (lazy loading por componente); há orientação de FOUC (carregar o CSS do core antes de exibir). `W:content-visibility` trata `content-visibility: auto` + `contain-intrinsic-size` como técnica **complementar** em seções criadas pela aplicação e diz que ela **não substitui paginação ou virtualização**: **`br-table` com milhares de linhas deve usar paginação ou janela virtual no consumidor**; `br-list`, `br-notification`, `br-carousel` com dados extensos: janela controlada na aplicação; `br-select` com muitas opções: janela virtual interna + busca remota. `br-table` e `br-pagination` têm `loading` (linhas anteriores permanecem; paginação com `aria-busy`; modo `controlled` confirma a página só após a resposta). As páginas de `br-table` importam blocos "Rolagem virtual e cabeçalho fixo" e "Dados remotos", e `br-list` um bloco "Performance": **conteúdo indisponível**. Não há benchmark nem limite numérico publicado; custo de um shadow root por elemento em milhares de células: [Pendente de confirmação: medir com os dados reais].

**(5) WBC 2.x × core 3.x.** `W:cdn` carrega `core@3/dist/core.min.css` com `webcomponents@2/.../webcomponents.esm.js`, e `W:dados-remotos` manda instalar os dois pacotes e deixar o npm resolver o peer `@govbr-ds/core`. Isso indica que **a linha 2.x é pensada para o core 3.x**, mas **nenhum arquivo lido declara a faixa exata do peer** nem confirma o par 2.7.0 + 3.7.0 [Pendente de confirmação: `peerDependencies` de `@govbr-ds/webcomponents@2.7.0`].

**(6) Navegadores** (`W:compatibilidade`). Alvo ES2020, Custom Elements, Shadow DOM e **Baseline Widely Available** ("a lista pode mudar entre releases"); **IE não é suportado**. Contratos críticos testados em Chromium, Firefox e WebKit, sem suporte declarado a uma versão específica; sem versões mínimas numéricas. O core 3.7.0 declara Chrome 49+, Edge 14+, Firefox 67+, Safari 11+ (`S:guias/navegadores-suportados`): o WBC é **mais restritivo**.

**(7) Acessibilidade e teclado** (`W:acessibilidade`, `W:contrato-plataforma-e-apg`, `W:movimento-e-elevacao-webcomponents`). Referência WCAG 2.2 AA; HTML nativo quando há equivalente, padrões WAI-ARIA APG nos demais; axe não substitui teste manual. Teclado documentado: botão/link (Tab, Enter/Espaço); checkbox/switch (Espaço); radio (setas, Home/End, roving tabindex); select/combobox (setas, Enter/Espaço, Escape, "conforme o modo"); collapse (Enter/Espaço, `details`/`summary`); modal (foco inicial, contenção, Escape, retorno ao acionador); **tabs/menu (roving tabindex, setas, Home/End)**; tooltip (abre por foco/hover, fecha com Escape); breadcrumb e paginação com `nav` + listas e **`aria-current` na página atual**; table: tabela nativa, grid só se editável. Movimento: transições só com `prefers-reduced-motion: no-preference`; com `reduce`, superfícies abrem sem transição, Loading estático e Carousel sem autoplay (isso cobre a lacuna da regra 84 no WBC). Cuidado do consumidor: relações ARIA por ID **não atravessam** Shadow Roots (rótulos e `aria-describedby` do painel precisam de atenção); label visível, erro em texto e `aria-invalid`. Checklist exigido pelo WBC: NVDA+Firefox, VoiceOver+Safari, TalkBack+Chrome. Isto é o **contrato declarado**; o comportamento real não foi testado aqui.

**(8) Dados e eventos em JS puro** (`W:frameworks/javascript`, `W:eventos`, `W:ciclo-de-vida`, `W:dados-remotos`, `W:formularios`). Componentes **não fazem HTTP**: a aplicação faz `fetch` e atribui **propriedades** (preferir `el.prop = x` a `setAttribute`; booleano HTML é por presença: `disabled="false"` ainda desabilita). Eventos: `input`/`change`/`focus`/`blur`/`click` nativos redisparados no host (estado em `event.target.value|checked|files`); `CustomEvent` de domínio `br<Componente><Ação>` (`brSelectSearch` com `detail.query`, `brPaginationPageChange`, `brPaginationPerPageChange`, `brNavigate`, `brSelectValidationChange`). Métodos assíncronos (Promises): `select.setOptions(items)`, `checkValidity()`, `reportValidity()`, `setCustomValidity()`, `getValidationState()`, `validate()`; `await el.componentOnReady()` antes de medir o DOM após mudar estado (renderização assíncrona). Escritas programáticas **não emitem** eventos. Busca remota documentada: debounce, `AbortController`, id de requisição, `select.loading = true`, `setOptions()`. Links: `navigation-mode="native"` (padrão) ou `event` (`brNavigate`, cancelável). **Inconsistência da doc:** `W:cdn` usa `…/dist/webcomponents/webcomponents.esm.js`, enquanto `W:frameworks/javascript` usa `@govbr-ds/webcomponents/loader` + `dist/webcomponents.css` (para bundler); em HTML puro, o único caminho sem build documentado é o do CDN.

### 7.3 Prós, contras e riscos

| Critério | CSS core 3.7.0 (atual) | Web Components (série 2.x) |
|---|---|---|
| Alinhamento oficial | Versão exibida no site; marcação oficial | "Abordagem recomendada" para projetos modernos (`S:como-comecar/fluxo-dev`); "integração base" do WBC |
| Peso/dependências | 1 CSS + 1 JS via CDN, sem arquivo novo | **Core CSS + WBC** (duas dependências); peers `floating-ui`, `date-fns`, `iconify-icon` no npm; entrada ESM `type="module"` |
| Estilo próprio | Mesmo DOM: CSS do painel aplica direto | Shadow DOM: só variáveis, parts e tokens documentados; tema `--br-theme-*` **em beta** |
| Tabela grande (JS) | HTML/DOM próprio; collapse e paginação com init manual (111) | `br-table` existe, mas milhares de linhas exigem paginação/virtualização no consumidor; linha expansível com timeline: [Pendente de confirmação] |
| Autocomplete de município | Implementação própria sobre `br-input` + `br-list`; ARIA manual (154) | `br-select` filtrável + busca remota + `setOptions()` + janela virtual; teclado "setas, Enter/Espaço, Escape" documentado; destaque das letras e UF continuam do projeto; ARIA exata: [Pendente de confirmação] |
| Acessibilidade | Completar ARIA de abas e paginação à mão (141, 161) | Contrato APG declarado (abas com roving tabindex, `aria-current` na paginação); **não testado aqui**; IDs ARIA não cruzam Shadow DOM |
| Movimento reduzido | Implementar à mão (84) | Documentado e embutido |
| CSP | Só liberar o CDN | Guia genérico; `unsafe-inline`/nonce **não esclarecidos**; inline do painel é lacuna |
| Navegadores | Chrome 49+, Edge 14+, Firefox 67+, Safari 11+ | Baseline Widely Available, sem versões mínimas |
| Maturidade | 3.7.0 estável; 4.0 em `next` | Série 2.x recente; tema por tokens em beta; props legadas depreciadas; guias com pontos de entrada diferentes e `@3`/`@2` em vez de versão exata |
| Testes | Seletores `.br-*` atuais | O próprio WBC usa `queryPiercing` para atravessar Shadow DOM nos testes; seletores/`evaluate` do painel precisariam ser revistos |

**Riscos (ordem de impacto):** (1) identidade própria do painel (farol, timeline, KPI, badge "Agora") não cabe em parts/variáveis; (2) tabela grande e re-render por filtro: custo por elemento customizado desconhecido e virtualização por conta do projeto; (3) CSP com `<script>`/`<style>` inline do `index.html` sem orientação oficial; (4) API jovem (tema beta, aliases depreciados, docs por componente geradas) e par WBC 2.7.0 + core 3.7.0 sem confirmação por texto; (5) o WBC é compatível com a regra "tudo em `index.html`, JS puro" (funciona por CDN sem framework), mas dobra as dependências externas (jsDelivr + cdnjs + fontes).

### 7.4 Recomendação revisada

1. **Produção: manter `index.html` em HTML/CSS/JS puro com `@govbr-ds/core@3.7.0` fixo.** O WBC não elimina o código do projeto (tabela, timeline, farol, KPI, regra do dia 15 seguem do painel), não resolve o isolamento de estilo dos elementos visuais próprios e deixa CSP, desempenho em tabela grande e o par de versões sem confirmação documentada.
2. **Spike: recomendado, mas restrito a formulário e sobreposição**, em arquivo separado (nunca no `index.html` de produção): `br-select` filtrável (município) com `setOptions()`, `br-message`, `br-modal` e `br-pagination` (modo `controlled` com `loading`). **Fora do spike:** `br-table`, `br-tab`, `br-tag` do farol e `br-card`. Critérios de aceite: par `webcomponents@2.7.0` + `core@3.7.0` sem erro; CSP real do Render com o `<script>`/`<style>` inline do painel; ARIA e teclado do select no NVDA+Firefox; custo com a lista real de municípios; aparência só com `--br-*` e parts documentadas.
3. **CDN (D1):** a documentação oficial do WBC recomenda **jsDelivr com versão exata** para core e WBC (`W:cdn`, `W:carregamento-e-csp`: "Fixe uma versão explícita. Não use `latest` em produção"). Os exemplos oficiais usam `@3` e `@2`; o painel deve usar versão exata (ex.: `@3.7.0`).
4. Reavaliar quando: o tema por tokens sair de beta, os blocos de teclado/ARIA/parts por componente puderem ser lidos (build do site ou pacote npm) e o spike passar nos critérios acima.


## 8. Pontos de decisão para o agente implementador

- Autocomplete: implementar sobre `br-input` + `br-list`, com ordem alfabética, loading e mensagem "sem resultado" (154); validar ARIA manualmente.
- Farol: usar tag de texto com ícone; o círculo de status do DS é só cor (121).
- ARIA de abas e paginação não vem nos exemplos oficiais; acrescentar (141, 161).
- Tooltip está obsoleto: decidir substituto sem esconder informação essencial (200).
- Subabas Ativos/Encerrados: não aninhar abas (142); só aplicar se o painel hoje aninha.
- Breadcrumb só se houver hierarquia real (105).
- Estado vazio e "Limpar Filtros" conforme padrão (306); botões em Title Case (180).
- Reconciliar `CLAUDE.md`/`docs/design.md` com Rawline + Raleway (9).
- Web Components: manter core 3.7.0 em produção; spike isolado só com select filtrável, message, modal e paginação (seção 7.4).

## 9. Verificações adicionais (tabela responsiva, abas, combobox, regiões ao vivo)

Acrescentadas em 21/11/2026 durante a auditoria do protótipo. A origem é WCAG/WAI-ARIA e o próprio projeto, **não** uma página do site gov.br; o site não descreve tabela-em-cartões nem combobox (ver 113, 154). Marcadas `[Pendente de confirmação: site]`.

| # | Categoria | Regra | Página de origem | Como verificar |
|---|---|---|---|---|
| 600 | Tabela responsiva | O site documenta, para 4 colunas, **rolagem horizontal** (113, 42). Transformar a tabela em cartões no celular é decisão do projeto, a registrar como desvio consciente; a página "mobile" do DS não foi lida | S:components/table; [Pendente de confirmação: site] | Comparar com a página mobile do DS |
| 601 | Tabela responsiva | Se `display:block` for aplicado a `table/tr/td`, restaurar a semântica com `role="table|rowgroup|row|columnheader|cell"` (navegadores podem descartar o papel de tabela); manter o cabeçalho visualmente oculto, mas presente | WCAG 1.3.1; [Pendente de confirmação: site] | NVDA/VoiceOver: anuncia linha e coluna |
| 602 | Tabela | Região com rolagem horizontal precisa ser alcançável por teclado: `tabindex="0"`, `role="region"` e `aria-label` | WCAG 2.1.1; [Pendente de confirmação: site] | Tab até a tabela e rolar com setas |
| 603 | Tabela | Primeira célula que identifica a linha como `th scope="row"` | WCAG 1.3.1; S:components/table (marcação não capturada) | Inspecionar |
| 604 | Regiões ao vivo | Não envolver tabela inteira em `aria-live`; anunciar só um resumo curto ("N resultados") em região `role="status"` própria; não aninhar `role=status` dentro de `aria-live` | WCAG 4.1.3; [Pendente de confirmação: site] | Leitor de tela ao trocar aba/filtro |
| 605 | Foco após re-render | Se a ação remove o elemento focado (ex.: "Mostrar Mais"), devolver o foco a um destino lógico | WCAG 2.4.3; S:padroes/design/navegacao (305) | Teclado |
| 606 | Combobox | Padrão ARIA 1.2 (`combobox` + `listbox` + `aria-activedescendant`), fechar com Esc **e ao perder o foco/Tab/clique fora**, anunciar quantidade de resultados, texto auxiliar do mínimo de letras | WAI-ARIA APG; S:components/input (154) | Teclado + leitor de tela |
| 607 | Tabs | Com `ul[role=tablist]`, os `li` filhos precisam de `role="presentation"` (ou usar `button`s direto no `tablist`) | WAI-ARIA APG; S:components/tab (141) | Validador ARIA |
| 608 | Estado vazio | "Sem resultado" com filtros ativos oferece o botão **"Limpar Filtros"** (306). Isto não conflita com 155, que trata de formulário de entrada de dados, não de filtros de consulta | S:padroes/design/emptystates | Filtrar até zerar |

## Pendências (resumo)

Aba "Códigos" e "Acessibilidade" de cada componente (classes e ARIA oficiais); nomes das classes de grade; tabela de contraste do DS; critérios WCAG citados por número; `prefers-reduced-motion` no site; papel ARIA exato de messages vazias; combobox/autocomplete ARIA; ARIA obrigatório de abas e paginação; SRI/fallback de CDN; Popper dentro do `core.min.js`; substituto do tooltip; páginas não lidas aqui (ilustração, onboarding, dropdown, mobile, templates, componentes que o painel não usa); do WBC: blocos gerados por componente (parts, variáveis CSS, teclado, ARIA, "Dados remotos", "Rolagem virtual"), peer exato de core para `@govbr-ds/webcomponents@2.7.0`, CSP real (`unsafe-inline`/nonce), versões mínimas de navegador, desempenho com milhares de linhas, tooltip no WBC e `iconify-icon` via CDN. Atualizar este checklist quando o DS 4.x sair de `next` ou quando as abas de código/acessibilidade forem capturadas.


## 10. Desvios conscientes do protótipo (a confirmar com a SAPS)

- ~~Cartões no celular e até 991 px~~ (resolvido em 21/11/2026): tabelas voltaram à grade; ver seção 12.
- Rawline por CDN de terceiro (fonts.cdnfonts.com); o index.html único não carrega a fonte local. [Pendente de confirmação]
- Campos e selects nativos estilizados, sem r-input e r-select; abas e botões com JS próprio, sem core.min.js.
- Mostrar Mais com contagem Exibindo X de Y, no lugar de paginação contextual.
- Cartões próprios (cred-card) com sombra e raio dos tokens, sem .br-card.


## 11. Template Base (`S:templates/base`, lido em 21/11/2026)

A página só traz conteúdo quando renderizada em navegador (o leitor de páginas recebe só o título). Abas lidas: Visão Geral e Códigos. Utilitários CSS: o site traz só a visão geral (categorias); não há páginas de detalhe; as classes foram conferidas direto no `core.css` 3.7.0.

| # | Regra | Fonte | Situação no protótipo |
|---|---|---|---|
| 700 | Áreas: Cabeçalho (header, obrigatório), Conteúdo (obrigatório), Localização (breadcrumb, opcional), Navegação (menu, opcional), Rodapé (footer, obrigatório) | S:templates/base | Atende; sem breadcrumb nem menu |
| 701 | Estrutura de código: `div.template-base` > skip links, `header.br-header` (`mb-4`), `main.d-flex.flex-fill` > `.container-lg.d-flex` > `.row` > `.col.pb-5` > `#main-content`, `footer.br-footer` | S:templates/base (Códigos) | Aplicada. `.template-base` já faz coluna flex com `min-height` da janela (rodapé no fim da janela) |
| 702 | Largura: `container-lg` = fixa no desktop; `container-fluid` = fluida (sistemas que aproveitam a tela). Header, main e footer usam a mesma classe | S:templates/base | `container-lg` (fixa). Decisão pendente: o painel é um sistema; o DS indica fluida para esse caso |
| 703 | Espaços mínimos: cabeçalho→conteúdo `--spacing-scale-3x` (24px, `mb-4`); conteúdo→rodapé `--spacing-scale-5x` (40px; o código de exemplo usa `pb-5` = 32px) | S:templates/base (Especificações) | 24px e 32px (código de exemplo) |
| 704 | Skip links: nav `br-skiplink` com itens para conteúdo, menu, busca e rodapé, com `accesskey` e numeração (n/total); âncoras apontam para ids existentes | S:templates/base (Melhores Práticas e Códigos) | Conteúdo e rodapé (2 itens; não há menu nem busca) |
| 705 | Quando o breadcrumb não basta, um título logo após a área de localização | S:templates/base | Título (h1) no header do DS |
| 706 | Grid 4/8 colunas sempre fluida; 12 colunas fluida ou fixa; a sangria (header e footer) continua, o conteúdo respeita a grid | S:templates/base | Atende |

Classes de espaçamento conferidas no `core.css` 3.7.0: `pt-3` = 16px, `mb-3` = 16px, `mb-4` = 24px, `pb-5` = 32px (n corresponde a (n-1) x 8px).
## 12. Revisão com o guia comum atualizado (21/11/2026)

Fonte: `02_PAGINAS_ONLINE/design_referencia/govbr/documentacao/` (README, guia-aplicacao, checklist, coleta de 21/11/2026, DS 3.7.0) e o agente `govbr-ds` atualizado. Decisão do usuário vigente: tabelas em grade, com rolagem somente quando necessária; não converter em cartões nem ocultar colunas.

| # | Item | Resultado | Evidência |
|---|---|---|---|
| 800 | Tabela principal e matriz por estratégia mantêm grade e todas as colunas em qualquer largura | Conforme | Teste em 320, 390, 575, 640, 768, 992, 1024, 1280, 1440 e 1920 px nas 3 abas: `table` permanece `display: table`, 7 colunas visíveis, matriz sem corte |
| 801 | Rolagem horizontal só dentro da tabela e só quando não cabe; página sem rolagem horizontal; região com `tabindex="0"`; primeira coluna fixa | Conforme | Rolagem interna em 320 e 768 px, nenhuma em 1280 px; `scrollWidth` da página igual à largura da janela em todas as larguras. Origem: S:components/table (Responsividade: grades 12/8 quebram o texto; grade de 4 colunas recomenda rolagem) |
| 802 | Dica ao passar o mouse: aparece também no foco, fecha com Esc e o ponteiro pode ir até ela sem fechar (WCAG 1.4.13) | Conforme (implementação própria) | Teste de hover, movimento até a dica e Esc |
| 803 | Componente Tooltip do DS é obsoleto no site; mantido por compatibilidade | Registrado | A dica é implementação própria, não o componente. [Pendente de confirmação com a SAPS] |
| 804 | Contraste de texto (AA) | Sem falhas na varredura automática (aba Encerrados, detalhe aberto, 1280 px) | Texto x fundo efetivo; não cobre todos os estados (foco, hover, aba Portarias) |
| 805 | IDs únicos | Conforme | Sem duplicados nas 3 abas e 10 larguras |
| 806 | Template base (ver seção 11) e skip links | Conforme | Estrutura `template-base`; atalhos para conteúdo e rodapé |
| 807 | Movimento reduzido | Conforme | `prefers-reduced-motion` desliga animações e transições |
| 808 | Zoom de 200% | Aproximado | Equivale a 640 px de largura (item 800); não testado com zoom real do navegador |
| 809 | Leitor de tela e teste manual de teclado | Pendente | Não executado |


## 13. Aba Simulador (21/11/2026)

Componentes oficiais usados: `br-card` (card-header e card-content), `br-select` com `core.min.js` 3.7.0 fixo carregado como `type="module"` (S:components/select; plano B com campo nativo se o script não carregar), `br-table`, `br-tag` (cores `bg-success`, `bg-warning`, `bg-danger`, `bg-info`, `bg-gray-60`; texto escuro no amarelo) e `br-message info` (observações e estado vazio). Dados: `prototipo/simulador.json`, gerado de `simulador.xlsx` por `prototipo/gerar_simulador.py`, que valida a regra (CNES = M, M+1, M+2; parcelas M+2 a M+4; prazo = mês de M+2) e para com erro se algo não bater.

Verificado: seleção por mouse e por teclado (digitar para filtrar, setas e Enter), estado na URL, "Limpar Filtros", contraste das tags (mínimo 4,6:1), sem rolagem horizontal da página de 320 a 1920 px. Não verificado: leitor de tela. Decisão do usuário vigente: o visual segue o padrão gov.br DS, e o exemplo anexado serviu só de referência de conteúdo. [Pendente: os filtros do painel principal ainda usam campo nativo, não `br-select`.]

## 14. Páginas de represados refeitas com componentes oficiais (21/11/2026)

`represados-estrategia.html` e `represados.html` deixaram de ter só o tema aplicado: agora seguem o template base (`br-skiplink`, `br-header` com marca SUS, `main` com `container-lg`, `br-footer`) e usam `br-tab` (visões), `br-card` (indicadores e seções), `br-button`, `br-input`, `br-table` e `br-tag`. O menu lateral, o cabeçalho em degradê e os botões em pílula foram removidos. Dados, endpoints, cálculos, IDs e regras ficaram como estavam; o **Modo Tático** (botão e tela) foi preservado sem alterações.

Desvios conscientes: os filtros usam `<select>` nativo com o estilo de campo do DS (como no painel principal), porque as listas são preenchidas por script; o filtro de município continua um campo com lista própria (combobox); a ordenação das tabelas continua por clique no cabeçalho, sem acionamento por teclado [Pendente]; a cor vermelha do indicador "Represados Críticos" é semântica. Marca d'água "API KEY REQUIRED" no mapa vem do provedor dos tiles e já existia antes.

Verificado (Playwright/Edge, dados reais da API): sem rolagem horizontal da página de 320 a 1440 px, 1 único `h1`, abas e troca de visão, abertura e uso do diálogo de filtros, chips de filtro, Modo Tático abre, contraste sem falhas, sem erros de script. Não verificado: leitor de tela e zoom real.