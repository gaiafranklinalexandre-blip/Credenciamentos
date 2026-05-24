# Contexto do Projeto

## Objetivo

Painel web público de monitoramento dos **Novos Credenciamentos da Atenção Primária à Saúde (APS)**. Substitui o painel Power BI anterior. Desenvolvido por Franklin Santos (SAPS/Ministério da Saúde, 2026).

Permite que gestores municipais e federais consultem:
- quais municípios foram credenciados e em qual portaria;
- status do processo de homologação financeira (3 competências CNES + 3 parcelas);
- portarias publicadas e legislação de referência;
- visão gerencial via dashboard com mapa, gráficos e KPIs.

---

## Público e uso previsto

- Gestores do Ministério da Saúde / SAPS (uso diário de monitoramento).
- Gestores municipais (consulta do próprio município).
- Acesso público via URL do Render (sem login).

---

## Principais funcionalidades existentes

### Capa (tela inicial)
- Busca por município com autocomplete (destaque da letra digitada, UF, fecha ao clicar fora).
- Filtros hierárquicos: Região → UF → Macrorregião → Portaria → Ano.
- Filtros cascateiam dinamicamente; município digitado desabilita selects; select preenchido limpa município.
- Links de navegação: Dashboard, Legislação, Sobre o Painel.

### Painel (resultado da consulta)
- Aba **Ativos**: credenciamentos com `selecao_credenciamento = 'Ativo'` e `credenciado > 0`.
- Aba **Encerrados**: credenciamentos com `selecao_credenciamento != 'Ativo'`.
- Aba **Homologações Ativas**: card com 3 competências CNES + 3 parcelas financeiras, status temporal, tooltips, regra do dia 15.
- Aba **Homologações Encerradas**: mesmo layout, processo marcado como concluído.
- Aba **Portarias/Legislação**: vista geral com referências + portarias agrupadas por ano; vista município com linha do tempo.
- Paginação (20 por página), download CSV por aba.

### Dashboard
- Portarias ativas no topo (com prazo restante em dias).
- 4 KPIs com animação count-up: equipes credenciadas, municípios, portarias, registros.
- Mapa Leaflet coroplético por UF (5 tons de azul, tooltip com detalhes ao hover).
- Gráfico de barras por Região (Chart.js).
- Gráfico donut por Tipo (Chart.js).
- Ranking por UF com barras de progresso (tabela).
- Gráfico de barras por Portaria (quando há múltiplas).
- **Todos os dados calculados automaticamente** — sem listas fixas de portarias ou municípios.

---

## Estrutura atual do projeto

```
PAINEL CREDENCIAMENTO/
├── index.html                    # Frontend completo (HTML + CSS + JS)
├── sync.py                       # Sincroniza Excel → API PHP
├── sync.php                      # API PHP + MySQL (Hostinger, não versionado)
├── base_credenciamentos_2026.xlsx # Base principal (não versionada)
├── Portarias_APS_Database.xlsx   # Base de portarias (não versionada)
├── CLAUDE.md                     # Memória principal do projeto
└── docs/
    ├── contexto-do-projeto.md
    ├── gestao-dados.md
    ├── design.md
    ├── github-flow.md
    ├── deploy-render.md
    └── atualizacao-das-regras.md
```

---

## Decisões técnicas tomadas

| Decisão | Motivo |
|---|---|
| Vanilla JS, sem framework | Simplicidade, sem build step, deploy direto |
| Tudo em um único `index.html` | Render serve arquivos estáticos; sem servidor Node |
| Chart.js e Leaflet carregados sob demanda | Não penalizar carregamento da capa |
| CSS do Leaflet no `<head>` | Necessário para mapa renderizar sem flash |
| API pública para leitura, autenticada para escrita | Painel é público; sync.py precisa de chave |
| Paginação de 20 por página | Evitar travamento com 12.948 registros |
| Filtro base dashboard = apenas ativos | Dashboard é gerencial, não histórico |
| GeoJSON de UFs via fetch externo (GitHub codeforamerica) | Evitar embutir ~500KB no index.html |

---

## Decisões visuais tomadas

- Identidade visual do Governo Federal: azul `#1351B4`, fonte Raleway.
- Capa com fundo `#EAECF4`, círculos decorativos via `::before` e `::after`.
- Cards com `border-radius: 12px`, sombra suave, borda colorida lateral.
- Farol de prazo: verde (>30 dias), amarelo (≤30), vermelho (≤15), cinza (encerrado).
- Badge "Agora" com animação `pulso-agora` (fade in/out 2s).
- Homologações: grid 3 colunas (uma por competência), com seta `↓` entre CNES e parcela.

---

## Pontos sensíveis

- `sync.php` no Hostinger precisa ser atualizado **manualmente** — não está no GitHub.
- O typo `'3 competêcias'` (sem n) na base Excel é tratado no código via `isTipo3Comp()` — não corrigir na base sem atualizar a função.
- `DATA_PORTARIA_FALLBACK` no JS tem datas fixas para portarias 10345 e 10979 — atualizar ao adicionar portarias com data nula na base.
- GeoJSON externo do mapa pode falhar se o repositório codeforamerica sair do ar — pendente hospedar localmente.

---

## Pendências e melhorias futuras

- [x] ~~Hospedar GeoJSON das UFs localmente~~ — feito, arquivo em `docs/brazil-states.geojson`.
- [x] ~~Dia de corte para parcela~~ — confirmado **dia 15**. Código e docs atualizados.
- [ ] Adicionar filtragem por portaria dentro do dashboard (foi removida a pedido — reavaliar futuramente).
- [ ] Tooltip nativo do Leaflet com CSS customizado funciona mas pode quebrar em versões futuras da lib.
- [ ] `DATA_PORTARIA_FALLBACK` deve crescer manualmente — avaliar lógica automática.
- [ ] Power BI paralelo (`novo_painel.pbix`) ainda em desenvolvimento — medidas DAX sendo criadas em paralelo ao site.
