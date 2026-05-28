# Painel Represados — Solicitações Pendentes

## Objetivo

Dashboard de monitoramento em tempo real de solicitações represadas (pendentes) do sistema de credenciamento de equipes da Atenção Primária à Saúde (APS), consolidadas a partir de múltiplos relatórios exportados individualmente.

---

## Acesso e Autenticação

**URL**: `https://credenciamentos.onrender.com/represados.html`

**Autenticação**: Senha client-side (SHA-256 em sessionStorage)

**Usuários autorizados**: SAPS, Ministério da Saúde

**Nota sobre segurança**: Esta é uma aplicação interna. A senha protege contra acesso casual. Em produção, considerar autenticação OAuth ou mTLS se exposto publicamente.

---

## Estrutura da Base de Dados

Arquivo origem: **REPRESADO_2.xlsx** (17.982 linhas × 7 colunas)

Gerado automaticamente por script Python que consolida:
- Relatórios por tipo de equipe
- Campos normalizados e deduplicitados
- Data padronizada (DD/MM/YYYY → YYYY-MM-DD)
- Estratégia identificada (22 tipos possíveis)
- Status atual (SOLICITADA, CREDENCIADA, PUBLICADO EM PORTARIA)

### Colunas

| Coluna | Tipo | Descrição |
|---|---|---|
| IBGE | int | Código IBGE do município (7 dígitos) |
| Nº Solicitação | string | ID único (ex: AMPL.2026.000005833) |
| Data da Solicitação | date | Quando foi solicitado |
| Estratégia | string | Tipo de equipe/serviço (22 valores) |
| Status | string | Situação (SOLICITADA, CREDENCIADA, PUBLICADO EM PORTARIA) |
| Quantidade | int | Número de equipes/CNES |
| OBS | string | Observações específicas por estratégia |

### Estratégias (22 tipos)

**Equipes de Saúde da Família (eSF)**
- Saúde da Família — credenciamento convencional
- Saúde da Família Ribeirinha — territórios fluviais/costeiros
- Inclusão de Incentivo Quilombola - eSF — ampliação em quilombolas

**Equipes Multiprofissionais (eMulti)**
- eMulti Estratégica / Ampliada / Complementar / Ampliada Intermunicipal
- Multiprofissional — ampliação de componente já existente

**Saúde Bucal (eSB)**
- Saúde Bucal - 40 Horas
- Saúde Bucal com carga horária diferenciada

**Outros Serviços**
- Agente Comunitário de Saúde (ACS)
- Atenção Primária (eAP)
- Atenção Primária Prisional (eAPP)
- Consultório na Rua (eCR)
- Academia de Saúde
- Unidade Odontológica Móvel (UOM)
- Incentivo para Residência na Atenção Primária
- Pnaisari — unidades socioeducativas

**Alterações de Tipologia**
- eAP > eSF / eSF > eSFR / eSB CHD > eSB 40h / Alteração eAPP

---

## Arquitetura

```
REPRESADO_2.xlsx (local)
  ↓ sync.py (carga + normalização)
API PHP (sync.php) → MySQL (tabela represados)
  ↓ fetch
represados.html (Render)
  ↓ sessionStorage (auth)
Dashboard com KPIs, Mapa, Gráficos, Tabela
```

---

## Frontend — represados.html

### Estrutura

**Arquivo único** com HTML, CSS, JavaScript embutido (~1500 linhas)

```html
<!-- Cover page (senha) -->
<div id="capaSenha"> ... </div>

<!-- Dashboard (após login) -->
<div id="painelRepresentados">
  <!-- Header -->
  <!-- Filtros -->
  <!-- KPIs (4 cards) -->
  <!-- Mapa Leaflet -->
  <!-- Charts (3 gráficos) -->
  <!-- Tabela detalhada -->
</div>
```

### Componentes

#### 1. Autenticação
- Input de senha
- Hash SHA-256 comparado (client-side)
- SessionStorage para manter login
- Logout limpa sessão

**Senha padrão**: `password` (mude em produção alterando `SENHA_HASH` no código)

#### 2. Filtros Cascata
```
UF (dropdown) → Município (dinâmico)
Estratégia (dropdown)
Tipo de Solicitação (CRED/AMPL/TIPO/ADESAO/OUTRO)
Data Inicial e Final (date range)
Botão "Limpar Filtros"
```

Filtros aplica AND logic — todos devem ser satisfeitos.

#### 3. KPIs (4 Cards)
1. **Total de Represados** — contagem de solicitações únicas
2. **Quantidade Acumulada** — soma de equipes/CNES
3. **Distribuição Geográfica** — UF/Município afetados
4. **Tempo Médio em Fila** — dias desde data de solicitação até hoje

#### 4. Mapa Interativo (Leaflet)
- Mapa do Brasil com estados coloridos
- Escala de cores: branco (0) → azul claro → **azul escuro** (máximo)
- Tooltip: "SP — 150 represados"
- Hover: destaca estado

Usa GeoJSON `docs/brazil-states.geojson` (compartilhado com index.html)

#### 5. Gráficos (Chart.js)
- **Bar chart**: Represados por Estratégia (top estratégias)
- **Donut chart**: Distribuição por Status (3 categorias)
- **Pie chart**: Distribuição por Tipo de Solicitação (5 categorias)

Todos lazy-load Chart.js via CDN.

#### 6. Tabela
Município | Estado | Quantidade | Status | Tipos

Ordenada por Quantidade DESC. Mostra status e tipos de solicitação aggregados.

---

## Backend — sync.py + sync.php

### Fluxo de Sincronização

```bash
$ python sync.py
# 1. Lê base_credenciamentos_2026.xlsx (constrói IBGE map)
# 2. Lê REPRESADO_2.xlsx (17.982 registros)
# 3. Mapeia IBGE → UF/Município
# 4. Extrai tipo de solicitação do prefixo
# 5. Parseia data DD/MM/YYYY → YYYY-MM-DD
# 6. POST /sync.php?action=sync_represados
# 7. Tabela represados é truncated e repopulated
```

### sync.py — Funções

**`build_ibge_map(records)`**
- Extrai código IBGE, UF e Município dos credenciamentos carregados
- Cria dicionário para lookup rápido

**`load_represados(ibge_map)`**
- Lê REPRESADO_2.xlsx, Sheet1
- Normaliza tipos, datas, extrai request_type
- Retorna lista de dicts

**`sync_represados(records)`**
- POST para `API_URL?action=sync_represados`
- Envia com header `X-Api-Key: painel_cred_2026_key`
- Log do número de registros inseridos

### sync.php — Handlers

**CREATE TABLE `represados`**
```sql
CREATE TABLE IF NOT EXISTS represados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ibge INT,
    uf VARCHAR(5),
    municipio VARCHAR(100),
    nro_solicitacao VARCHAR(50),
    data_solicitacao DATE,
    estrategia VARCHAR(150),
    status VARCHAR(50),
    quantidade INT DEFAULT 0,
    request_type VARCHAR(20),
    obs TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
```

**`?action=sync_represados` (POST)**
- Requer autenticação: `X-Api-Key: painel_cred_2026_key`
- Trunca tabela, insere novos dados
- Retorna `{"success": true, "inserted": N}`

**`?action=represados` (GET)**
- Público (sem autenticação)
- Retorna JSON array de todos os represados
- Ordenado por `data_solicitacao DESC`

Código SQL completo em [`docs/sync-php-represados.md`](sync-php-represados.md).

---

## Regras de Negócio

### Tipo de Solicitação
Extraído do padrão do Nº Solicitação:
- **CRED** → Credenciamento novo
- **AMPL** → Ampliação
- **TIPO** → Alteração de tipologia
- **ADESAO** → Adesão a programa
- **OUTRO** → Padrão não reconhecido

### Quantidade
- Tipicamente 1 para tipologias/adesões
- > 1 para Multiprofissional (número de CNES distintos)

### Tempo em Fila
Calculado em tempo real:
```
dias = (hoje - data_solicitacao) em dias corridos
média = sum(dias) / contagem
```

### Data da Solicitação
Se vazio (tipologias/adesões): usa 01/01/2026 como marcador

---

## Operação

### Atualização de Dados

**Diariamente ou sob demanda:**
```bash
# 1. Substituir REPRESADO_2.xlsx (novo export do script)
# 2. Rodar sync.py
python sync.py
# 3. Verificar:
#    - Consola mostra "17.982 represados carregados" (ou número atualizado)
#    - "Represados sincronizados: XXXXX registros"
# 4. Dashboards recarregam automaticamente (fetch do API endpoint)
```

### Alteração de Senha

1. Editar `represados.html`
2. Localizar linha: `const SENHA_HASH = '5f4dcc3b5aa765d61d8327deb882cf99';`
3. Substituir hash (usar ferramenta MD5 online ou `echo -n "nova_senha" | md5sum`)
4. Commit e push (Render redeploya automaticamente)

### Troubleshooting

| Problema | Causa | Solução |
|---|---|---|
| Painel em branco | Falha no fetch API | Verificar `?action=represados` no sync.php |
| Mapa não renderiza | GeoJSON não encontrado | Verificar `GEOJSON_URL` path |
| Gráficos vazios | Sem dados filtrados | Verificar filtros, limpar cache |
| Senha não funciona | Hash incorreto | Recalcular MD5 da nova senha |
| 17.9k linhas caregando lentamente | Performance JS | Implementar virtual scrolling na tabela |

---

## Documentos Relacionados

- [`docs/sync-php-represados.md`](sync-php-represados.md) — Código PHP para Hostinger
- [`CLAUDE.md`](../CLAUDE.md) — Arquitectura geral e projetos em paralelo
- [`docs/deploy-render.md`](deploy-render.md) — Deploy no Render
- `sync.py` — Pipeline de dados

---

## Deploy

**Render**: Automático a cada push para `main`

**URL ao vivo**: `https://credenciamentos.onrender.com/represados.html`

**Banco de dados**: MySQL no Hostinger (sync.php)

Nenhuma configuração especial necessária — arquivo HTML estático é servido direto.
