# Design

## Panorama e estados independentes — 24/10/2026

O painel abre com todos os municípios consolidados por portaria e tabela de estratégias, com apenas o KPI de credenciamentos ativos. Portaria e estratégia recebem destaque visual. “Ver municípios” permite chegar ao detalhe preservando aba, portaria e estratégia. A linha do tempo termina com Concluído após a competência em que o total foi homologado, omitindo as posteriores. Verde indica conclusão, azul espera e âmbar/laranja atenção com ação municipal. As colunas U/V permanecem independentes. Referência vigente: [acompanhamento-integrado.md](acompanhamento-integrado.md). Teste atual: `tests/consolidado.cjs`. A revisão é somente de frontend e documentação; backend e bases não foram alterados. Descrições posteriores de cinco abas e dashboard são históricas.


## Identidade compartilhada dos painéis — 24/10/2026

Logo SUS padrão atualizado pelo usuário: `APS 360/LadingPage_Notas/sus-logo.png`. PNG integral incorporado no cabeçalho e no favicon como data URI, mantendo o frontend em arquivo único. Exibir com proporção original (76 px no computador, 58 px no celular), sem distorção ou fundo adicionado. Layout conferido em 320, 390, 768 e 1440 px.

Objetivo: aplicar o visual aprovado no painel de notas e landing page, mantendo as informações de credenciamento. Escopo: capa/consulta, resultados, homologações, portarias e dashboard, somente em index.html. Cabeçalho branco, azuis #1351b4/#071d41, sombras em camadas, degradês discretos, navegação lateral recolhível e campos/botões de filtros com altura efetiva de 48 px. Ícone SUS incorporado como favicon, sem novo arquivo remoto. Mantidas as identificações textuais atuais do projeto, sem reintroduzir referências institucionais removidas na revisão anterior.

No computador o menu ocupa 250 px (215 px em telas intermediárias), pode recolher e no celular vira gaveta. O cabeçalho antigo dos resultados fica oculto para evitar títulos duplicados. O dropdown existente continua oferecendo as cinco abas. As cores temporais e semânticas de status permanecem intactas; os KPIs de negócio do dashboard são preservados. Exemplos corretos: input e ações alinhados em 48 px, labels associados, contraste legível e regras responsivas para o conteúdo real. Evitar encolhimento por flex, campos menores que os botões e grades que esmagam nomes no celular.

Validação com dados reais e captura de resultados/mapa/gráficos em tests/visual_aps.cjs (requer Playwright/Edge no ambiente). Fontes do teste e documentação acompanham o commit; capturas locais em validacao_visual/ não são necessárias ao deploy.

## Paleta de cores (variáveis CSS)

```css
:root {
  --blue:        #1351B4;   /* cor principal — links, botões, destaques */
  --blue-hover:  #2670E8;   /* hover de botões azuis */
  --blue-light:  #D4E3FF;   /* badges, fundos sutis */
  --blue-bg:     #EEF2FB;   /* fundo de seções destacadas */
  --green:       #168821;   /* status positivo, ativo, homologado */
  --green-light: #E3F5E1;   /* fundo de badge verde */
  --red:         #E52207;   /* alerta, prazo crítico, erro */
  --red-light:   #FFE8E5;   /* fundo de badge vermelho */
  --yellow:      #FFCD07;   /* atenção, prazo médio */
  --yellow-light:#FFF5C2;   /* fundo de badge amarelo */
  --gray-bg:     #F0F0F5;   /* fundo geral da página */
  --gray-border: #E0E0E0;   /* bordas e separadores */
  --text:        #1C1C1C;   /* texto principal */
  --text-mid:    #666;      /* texto secundário, labels */
  --white:       #fff;
  --radius:      50px;      /* border-radius dos botões e inputs */
  --card-r:      12px;      /* border-radius dos cards */
  --shadow:      0 2px 12px rgba(0,0,0,0.07);
}
```

**Regra:** usar variáveis para as cores principais e semânticas. O bloco `estilo-integrado-aps` define também tons fixos dos degradês e sombras aprovados; manter esses tons centralizados no tema, sem reinterpretar as cores de status dos dados.

---

## Tipografia

- Fonte da interface: **Segoe UI, Arial, sans-serif**. Raleway permanece na configuração legada dos gráficos e tooltips, preservada nesta revisão.
- Tamanho base: `15px` no `body`.
- Títulos principais: 27–30 px na capa, adaptados ao celular; cabeçalho em 19–23 px.
- Labels de cards: `11px`, uppercase, `letter-spacing: .5px`.
- Valores de KPI: `28–36px`, `font-weight: 800`.

---

## Componentes visuais

### Cards
```css
background: var(--white);
border-radius: var(--card-r);   /* 12px */
box-shadow: var(--shadow);
```
- Cards de resumo: `border-left: 4px solid var(--blue)` (verde, amarelo ou vermelho para variações).
- Cards de credenciamento: `overflow: hidden`, header com borda inferior.

### Botões
- **Primário:** degradê azul do tema, `border-radius:30px`, texto branco; 48 px nas ações de consulta.
- **Outline:** `border: 2px solid var(--blue)`, `background: transparent`.
- **Limpar:** superfície branca, contorno azul discreto e texto azul, alinhado aos demais controles.

### Filtros / Selects
- Aparência personalizada com `appearance: none` + ícone SVG inline como `background-image`.
- Borda `1px solid var(--gray-border)` ou `rgba(19,81,180,0.2)` na capa.

### Badges
```css
.badge-blue   { background: var(--blue-light); color: var(--blue); }
.badge-green  { background: var(--green-light); color: var(--green); }
.badge-gray   { background: #f0f0f5; color: #666; }
```

### Farol de prazo
| Classe | Condição | Cor |
|---|---|---|
| `.farol-verde` | > 30 dias | Verde |
| `.farol-amarelo` | ≤ 30 dias | Amarelo |
| `.farol-vermelho` | ≤ 15 dias | Vermelho |
| `.farol-cinza` | Encerrado ou sem prazo | Cinza |

### Alertas de homologação
```css
.homol-alerta-azul    /* janela aberta, > 30 dias */
.homol-alerta-amarelo /* janela aberta, ≤ 30 dias */
.homol-alerta-vermelho/* janela aberta, ≤ 15 dias */
.homol-alerta-cinza   /* janela encerrada */
```

### Abas
- Cinco abas funcionais no dropdown do cabeçalho de resultados; opções existentes e regras de seleção preservadas.
- Menu lateral: consulta municipal, dashboard e portarias/legislação; seleção acompanha a área exibida.

### Animações
```css
/* Badge "Agora" piscando na timeline */
@keyframes pulso-agora {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.25; }
}
/* Duração: 2s ease-in-out infinite */

/* Spinner de loading */
@keyframes spin { to { transform: rotate(360deg); } }
/* 0.8s linear infinite */
```

---

## Dashboard

### KPI cards
- 4 cards com borda superior colorida (`border-top: 4px solid`).
- Cores: azul (padrão), verde, amarelo, roxo (`#6c3fc5`).
- Ícone decorativo de fundo: `position: absolute`, `opacity: .07`, `font-size: 42px`.
- Valor: `font-size: 36px`, `font-weight: 800`, com animação count-up ao abrir.

### Mapa Leaflet
- Tiles: CartoDB light sem rótulos (`light_nolabels`) — fundo neutro para destacar cores.
- 5 tons de azul para intensidade: `#f0f4ff` → `#dbeafe` → `#93c5fd` → `#3b82f6` → `#1351B4` → `#1e3a8a`.
- Tooltip personalizado com classe `.dash-mapa-tooltip` (sem borda, sombra suave, border-radius 10px).
- Scroll wheel zoom desabilitado (`scrollWheelZoom: false`).
- Centro: `[-14.5, -51.9]`, zoom inicial: `4`.

### Gráficos Chart.js
- Barras: `borderRadius: 6`, `borderSkipped: false`, sem legenda, grid sutil `#f0f0f5`.
- Donut: `cutout: '62%'`, legenda na base, `hoverOffset: 8`.
- Fonte: `Raleway` em todos os ticks e labels.

---

## Responsividade

Breakpoint principal: `max-width: 700px`.
- `.content`: padding reduzido para `14px 12px`.
- `.capa-nav`: oculto em mobile (`display: none`).
- `.resumo-grid`: 2 colunas.
- `.homol-comp-grid`: 1 coluna (de 3).
- `#dashMapa`: altura reduzida de 420px para 280px.
- `.dash-kpi-grid`: 2 colunas (de 4).

---

## Identidade visual institucional

- Preservar os textos atuais de identificação do projeto e o rodapé 2026, conforme a revisão anterior; não reintroduzir cabeçalhos removidos sem solicitação.
- Usar as fontes de interface e gráficos documentadas acima.
- Preservar a paleta semântica e o tema compartilhado aprovado.
- Manter o tom institucional: sem gradientes chamativos, sem animações excessivas.
- O painel deve parecer uma extensão digital do Governo Federal.
