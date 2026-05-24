# Gestão de Dados

## Bases utilizadas

| Arquivo | Aba/Sheet | Atualizado por |
|---|---|---|
| `base_credenciamentos_2026.xlsx` | `Base` | Franklin (manual, até dia 15-20 do mês) |
| `Portarias_APS_Database.xlsx` | Aba ativa (primeira) | Franklin (manual, ao publicar portaria) |

Ambos os arquivos **não são versionados** no GitHub (`.gitignore` ou omissão intencional).

---

## Fluxo de carregamento dos dados

```
1. Franklin atualiza o Excel localmente
2. Executa: python sync.py
3. sync.py lê os Excels com openpyxl
4. Envia via POST para sync.php (Hostinger) com X-Api-Key
5. sync.php trunca e reinsere na tabela MySQL
6. index.html faz fetch GET (?action=data e ?action=portarias) ao abrir
7. Dados ficam em memória no array `dados` e `portarias`
```

### Ações da API

| Action | Método | Auth | Descrição |
|---|---|---|---|
| `?action=sync` | POST | Sim | Trunca e insere 1º lote de credenciamentos |
| `?action=append` | POST | Sim | Insere lotes adicionais (sem truncar) |
| `?action=sync_portarias` | POST | Sim | Trunca e reinsere portarias |
| `?action=data` | GET | Não | Retorna todos os credenciamentos |
| `?action=portarias` | GET | Não | Retorna todas as portarias |
| `?action=stats` | GET | Não | Retorna estatísticas resumidas (não usado no front) |

---

## Campos críticos — base_credenciamentos_2026.xlsx

| Coluna Excel | Campo MySQL | Tipo | Uso no front |
|---|---|---|---|
| `UF` | `uf` | VARCHAR(5) | Filtro, autocomplete, mapa |
| `IBGE` | `ibge` | INT | Identificação |
| `Município` | `municipio` | VARCHAR(100) | Busca, autocomplete, agrupamento |
| `Região` | `regiao` | VARCHAR(50) | Filtro, gráfico dashboard |
| `Macrorregião` | `macrorregiao` | VARCHAR(100) | Filtro, card |
| `Tipo` | `tipo` | VARCHAR(100) | Lógica `isTipo3Comp()`, `isEstabelecimento()` |
| `Estratégia` | `estrategia` | VARCHAR(100) | Exibição |
| `Nome completo` | `nome_completo` | VARCHAR(200) | Card, homologação |
| `Portaria` | `portaria` | INT | Filtro, agrupamento, timeline |
| `Data` | `data_portaria` | DATE | Timeline, cálculo de competências |
| `Credenciado` | `credenciado` | INT | KPI, cards, homologação |
| `Homologação 1` | `homologacao_1` | INT | Card homologação, parcela 1 |
| `Homologação 2` | `homologacao_2` | INT | Card homologação, parcela 2 |
| `Homologação 3` | `homologacao_3` | INT | Card homologação, parcela 3 |
| `Seleção Credenciamento` | `selecao_credenciamento` | VARCHAR(100) | Filtro ativo/encerrado |
| `Ano` | `ano` | INT | Filtro, agrupamento |

---

## Campos críticos — Portarias_APS_Database.xlsx

| Coluna Excel | Campo MySQL | Tipo | Observação |
|---|---|---|---|
| `N_PORTARIA` | `n_portaria` | INT | "10.979" → remover pontos → 10979 |
| `DATA` | `data_portaria` | DATE | Formato YYYY-MM-DD |
| `ANO` | `ano` | INT | Ano da portaria |
| `TIPO_ATO` | `tipo_ato` | VARCHAR(100) | Ex: "Credenciamento", "Homologação" |
| `EQUIPE_SERVICO` | `equipe_servico` | VARCHAR(100) | Ex: "eAP > eSF" |
| `LINK` | `link` | TEXT | URL do Diário Oficial |
| `DESCRICAO` | `descricao` | TEXT | Texto descritivo |

---

## Padronizações críticas

### N_PORTARIA
O Excel usa formato `"10.979"` (string com ponto como separador de milhar). O `sync.py` normaliza:
```python
raw = str(r.get('N_PORTARIA') or '').replace('.', '').replace(',', '').strip()
n_portaria = int(raw)
```
No front, a comparação é sempre `Number(p.n_portaria)` vs `Number(d.portaria)`.

### Tipo de credenciamento
A função `isTipo3Comp()` aceita variações e o typo da base:
```javascript
// Aceita "3 competências", "3 competêcias" (typo), "3 comp", etc.
return t.startsWith('3') && (t.includes('compet') || t.includes('comp'));
```
**Não corrigir o typo na base sem atualizar esta função.**

### selecao_credenciamento
- `'Ativo'` → credenciamento ativo (aparece nas abas Ativos, Homologações Ativas, Dashboard).
- Qualquer outro valor → encerrado/finalizado.
- Comparação no front: `.toLowerCase() === 'ativo'`.

### Data da portaria
- Formato MySQL: `YYYY-MM-DD`.
- Fallback no front para portarias com data nula:
```javascript
const DATA_PORTARIA_FALLBACK = { 10345: '2026-03-13', 10979: '2026-05-01' };
```
Atualizar este objeto ao adicionar portarias com data nula na base.

---

## Regras de negócio de datas

| Evento | Mês |
|---|---|
| Publicação da portaria | M |
| 1ª Competência CNES | M |
| 2ª Competência CNES | M+1 |
| 3ª Competência CNES | M+2 |
| 1ª Parcela financeira | M+2 |
| 2ª Parcela financeira | M+3 |
| 3ª Parcela financeira | M+4 |

**Regra do dia 15:** Franklin atualiza o painel até o dia 15 de cada mês. Zero em parcela:
- Antes do dia 15 do mês da parcela → "Aguardando processamento" (normal, esperado).
- Após o dia 15 → "Parcela processada — 0 homologados" (real zero, problema de cadastro CNES).

No código JS: `const homolDia20 = new Date(comp.aHomol, comp.homol - 1, 15)` — confirmado dia 15.

---

## Riscos ao alterar dados

| Alteração | Risco |
|---|---|
| Renomear coluna no Excel | Quebra mapeamento no sync.py |
| Mudar valor de `Tipo` | Quebra `isTipo3Comp()`, cards e abas erradas |
| Mudar valor de `Seleção Credenciamento` | Registros somem da aba errada |
| Portaria com data nula sem fallback | Timeline não renderiza, farol some |
| N_PORTARIA com formato diferente | Vínculo portaria ↔ portarias_aps quebra |
| Municípios com acentuação inconsistente | Autocomplete não encontra |

---

## Validações antes de rodar sync.py

- [ ] A aba da base se chama exatamente `Base`.
- [ ] Todas as colunas obrigatórias existem com os nomes exatos.
- [ ] N_PORTARIA não tem espaços extras.
- [ ] Datas estão em formato reconhecível pelo openpyxl (datetime ou string YYYY-MM-DD).
- [ ] `Seleção Credenciamento` só contém `'Ativo'` ou `'Finalizado'`.
