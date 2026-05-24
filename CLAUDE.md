# CLAUDE.md — Memória Principal do Projeto

> Leia este arquivo antes de qualquer mudança relevante no projeto.
> Consulte os documentos em `docs/` para detalhes específicos.

---

## Objetivo do painel

Painel web público de monitoramento dos **Novos Credenciamentos da Atenção Primária à Saúde (APS)** — substituto do painel Power BI anterior. Desenvolvido pela SAPS/Ministério da Saúde. Permite consultar municípios credenciados, status de homologações, portarias publicadas e visualizar dashboard gerencial.

---

## Stack

| Camada | Tecnologia |
|---|---|
| Frontend | HTML + CSS + JavaScript (vanilla, sem framework) |
| Fonte de dados | API PHP (Hostinger) → MySQL |
| Sincronização | Python (`sync.py`) + openpyxl + requests |
| Versionamento | Git → GitHub (`gaiafranklinalexandre-blip/Credenciamentos`) |
| Deploy | Render (auto-deploy no push para `main`) |
| Gráficos | Chart.js 4.4.3 (carregado sob demanda via CDN) |
| Mapa | Leaflet 1.9.4 (carregado sob demanda via CDN) |
| Tipografia | Raleway (Google Fonts) |

---

## Arquitetura atual

```
Excel (base_credenciamentos_2026.xlsx + Portarias_APS_Database.xlsx)
  ↓ sync.py (Python)
API PHP (sync.php — Hostinger, NÃO está no GitHub)
  ↓ MySQL
index.html (Render) ← fetch API em tempo real
```

- `index.html` é o único arquivo de frontend — tudo em um único arquivo (HTML + CSS + JS).
- `sync.php` está hospedado no Hostinger e precisa ser atualizado **manualmente via painel Hostinger**. Não está no repositório GitHub.
- O deploy no Render é **automático** a cada push para `main`.

---

## Arquivos críticos

| Arquivo | Localização | Observação |
|---|---|---|
| `index.html` | Raiz do repo | Frontend completo |
| `sync.py` | Raiz do repo | Sincroniza Excel → API |
| `sync.php` | Hostinger (manual) | API PHP + MySQL |
| `base_credenciamentos_2026.xlsx` | Raiz local | Base principal — não versionar |
| `Portarias_APS_Database.xlsx` | Raiz local | Base de portarias — não versionar |

---

## Funcionalidades que não devem ser quebradas

1. **Autocomplete de município** — busca por nome, destaca letras, mostra UF, fecha ao clicar fora.
2. **Hierarquia de filtros** — selects cascateiam dinamicamente; município digitado desabilita selects; select preenchido limpa campo município.
3. **Abas do painel** — Ativos, Encerrados, Homologações Ativas, Homologações Encerradas, Portarias.
4. **Card de homologação** — mostra 3 competências CNES + 3 parcelas financeiras com status temporal e tooltips.
5. **Regra do dia 15** — zero antes do dia 15 do mês da parcela = aguardando; zero após dia 15 = processado sem homologação.
6. **Regra 3 competências** — portaria mês M → CNES M, M+1, M+2 → parcelas M+2, M+3, M+4.
7. **Dashboard** — KPIs, mapa Leaflet por UF, gráficos Chart.js, portarias ativas no topo — baseado apenas em registros `selecao_credenciamento = 'Ativo'`.
8. **Animação "Agora"** — badge verde piscando (`pulso-agora`) na timeline de competência atual.
9. **Vista município vs. vista geral** — comportamento diferente dependendo de `municipioSel`.
10. **Download CSV** — botão ao final de cada aba paginada.

---

## Regras principais de desenvolvimento

- Não usar frameworks JS (React, Vue, etc.) — o projeto é vanilla JS intencional.
- Não criar arquivos CSS ou JS separados — tudo permanece em `index.html`.
- Não versionar arquivos `.xlsx`, `desktop.ini`, `~$*`, `.pbix`.
- `sync.php` nunca vai para o GitHub — é atualizado manualmente no Hostinger.
- Não alterar regras de negócio (competências, parcelas, dia 15) sem documentar impacto.
- Ao editar `index.html`, sempre fazer commit e push para `main` — o Render faz o deploy automaticamente.
- Chart.js e Leaflet são carregados **sob demanda** (só quando o dashboard é aberto). Não mover para o `<head>`.
- O CSS do Leaflet está no `<head>` via `<link>` — necessário para o mapa renderizar corretamente.

---

## Regra de preservação de contexto

Antes de mudanças relevantes:
1. Revisar o histórico da conversa atual.
2. Ler este `CLAUDE.md`.
3. Consultar o documento específico em `docs/` para a área afetada.
4. Registrar decisões importantes para continuidade futura.

---

## Regra de criação de novas regras

Criar nova regra quando:
- a decisão for recorrente;
- evitar erro já ocorrido;
- afetar dados, design, GitHub, deploy ou arquitetura;
- padronizar comportamento futuro.

Antes de criar:
- verificar se já existe documento semelhante em `docs/`;
- atualizar documento existente quando fizer mais sentido;
- criar novo arquivo apenas se o tema for distinto;
- registrar a nova regra neste `CLAUDE.md`.

Toda nova regra deve conter: título, objetivo, quando aplicar, instruções práticas, exemplos corretos, exemplos a evitar, arquivos impactados.

---

## Regra de atualização das regras

Sempre que o contexto do projeto mudar:
1. Revisar o estado atual da conversa e do código.
2. Identificar documentos impactados em `docs/`.
3. Atualizar todos os arquivos relevantes.
4. Atualizar este `CLAUDE.md` quando houver mudança estrutural.
5. Remover regras duplicadas, conflitantes ou obsoletas.
6. Manter a documentação sincronizada com o código atual.

---

## Dois projetos em paralelo

Este repositório documenta **dois projetos simultâneos**:

| Projeto | Status | Arquivo principal |
|---|---|---|
| **Site web** (substituto) | Em desenvolvimento e validação | `index.html` |
| **Power BI** (legado ativo) | Em uso até validação completa do site | `novo_painel.pbix` (local) |

O Power BI será descontinuado quando o site estiver totalmente validado pela SAPS. Ambos compartilham a mesma base de dados Excel e as mesmas regras de negócio.

---

## Documentos de referência

### Site
| Documento | Conteúdo |
|---|---|
| [`docs/contexto-do-projeto.md`](docs/contexto-do-projeto.md) | Objetivo, funcionalidades, decisões técnicas e visuais |
| [`docs/gestao-dados.md`](docs/gestao-dados.md) | Bases, fluxo de dados, campos críticos, validações |
| [`docs/design.md`](docs/design.md) | Paleta, tipografia, CSS, componentes visuais |
| [`docs/github-flow.md`](docs/github-flow.md) | Branches, commits, arquivos ignorados |
| [`docs/deploy-render.md`](docs/deploy-render.md) | Deploy, validação, rollback |
| [`docs/atualizacao-das-regras.md`](docs/atualizacao-das-regras.md) | Quando e como atualizar a documentação |

### Power BI
| Documento | Conteúdo |
|---|---|
| [`docs/powerbi/contexto-powerbi.md`](docs/powerbi/contexto-powerbi.md) | Status, objetivo, relação com o site, critério de descontinuação |
| [`docs/powerbi/medidas-dax.md`](docs/powerbi/medidas-dax.md) | Todas as medidas DAX criadas, regras e erros já ocorridos |
