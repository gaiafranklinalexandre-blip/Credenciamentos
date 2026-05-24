# Design

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

**Regra:** nunca usar cores hardcoded fora das variáveis. Sempre referenciar via `var(--nome)`.

---

## Tipografia

- Fonte: **Raleway** (Google Fonts) — pesos 400, 500, 600, 700, 800.
- Tamanho base: `14px` no `body`.
- Títulos principais: `clamp(32px, 5vw, 64px)` na capa.
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
- **Primário:** `background: var(--blue)`, `border-radius: var(--radius)`, `color: #fff`.
- **Outline:** `border: 2px solid var(--blue)`, `background: transparent`.
- **Limpar:** `background: none`, `color: var(--red)`, apenas texto.

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
- Barra fixa com `border-bottom: 2px solid var(--gray-border)`.
- Aba ativa: `border-bottom: 3px solid var(--blue)`, `color: var(--blue)`.
- Separadores com `.aba-sep` (linha vertical de 1px).

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

- Sempre incluir "MINISTÉRIO DA SAÚDE · SAPS · 2026" no rodapé da capa.
- Não usar outras fontes além de Raleway.
- Não usar cores fora da paleta definida.
- Manter o tom institucional: sem gradientes chamativos, sem animações excessivas.
- O painel deve parecer uma extensão digital do Governo Federal.
