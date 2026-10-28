# Consulta rápida de pagamentos — CGFAP (indexar_parcela.py)

## Objetivo

Responder perguntas rápidas sobre os relatórios de validação de pagamento da APS (pasta `Y:\DESCO\COORDENAÇÕES\CGFAP\000 - PAGAMENTO\2026\Parcela_XX_2026`) sem reabrir dezenas de planilhas de centenas de milhares de linhas cada vez — ex.: "qual CNES recebeu mais nesta parcela", "quantas equipes foram homologadas", "quanto um CNES específico recebeu e por quê".

Este fluxo é **independente** do painel de credenciamentos (`index.html`/`represados-estrategia.html`) — não lê nem escreve nos mesmos bancos. É uma ferramenta de consulta local, para análises pontuais sob demanda.

---

## Como usar

```bash
python indexar_parcela.py "Y:\DESCO\COORDENAÇÕES\CGFAP\000 - PAGAMENTO\2026\Parcela_10_2026"
```

Gera/atualiza `banco_parcelas.db` (SQLite) na raiz do projeto. Rodar de novo com a mesma pasta reprocessa só aquela parcela (apaga e reinsere as linhas daquela `pasta_parcela`, preservando parcelas de outros meses já indexadas — o banco cresce mês a mês).

Depois, qualquer pergunta é uma consulta SQL direta na tabela `fatos`:

```python
import sqlite3
conn = sqlite3.connect("banco_parcelas.db")
conn.execute("""
    SELECT cnes, uf, municipio, SUM(valor_total) AS total
    FROM fatos
    WHERE valor_total IS NOT NULL AND pasta_parcela = 'Parcela_10_2026'
    GROUP BY cnes ORDER BY total DESC LIMIT 10
""").fetchall()
```

Dois exemplos (ranking de CNES por valor, contagem de homologações) já rodam automaticamente ao final do script.

## ⚠️ Nunca versionar `banco_parcelas.db`

Este repositório (`Credenciamentos`) faz deploy automático para um site **público** no Render a cada push. `banco_parcelas.db` contém dados internos de pagamento por CNES/equipe — **nunca pode ir para o GitHub**. Já está protegido pelo `.gitignore` (`*.db`). `indexar_parcela.py` (só código, sem dados) pode ser versionado normalmente.

---

## Por que isso sobrevive a mudança de nome de coluna/arquivo entre meses

A pasta `Parcela_XX_2026` muda de conteúdo todo mês: nomes de arquivo mudam (`rel_validacao_acs_...-20261006.xlsx` vira outra coisa no mês seguinte), e colunas às vezes são renomeadas. Duas decisões tornam o indexador resiliente a isso:

1. **A estratégia vem do nome da PASTA** (`ACS`, `eSF_eAP`, `eMulti`, `eSB`, `eCR`, `eAPP`, `eSFR`, `IAF`, `Microscopista`, `pnaisari`, `Quilombola`, `Residentes`, `UBSF`, `UOM`, `academia-saude`, `implantacao`, `_Ajustes`...), não do nome do arquivo. É o identificador mais estável que existe nessas bases.
2. **Colunas são detectadas por padrão de nome**, não por nome exato (`detectar_colunas()` em `indexar_parcela.py`):
   - UF: `SG_UF` / `UF`
   - Município (nome): começa com `NO_` e contém `MUNICIPIO`
   - Município (IBGE): contém `MUNICIPIO` e (`IBGE` ou começa com `CO_`)
   - Competência: contém `COMP` e `CNES`, ou começa com `NU_COMP`
   - Parcela: contém `PARCELA` e começa com `NU_`/`CO_`
   - **CNES (código do estabelecimento): começa EXATAMENTE com `CO_CNES`** — ver armadilha abaixo
   - Equipe: começa com `CO_EQUIPE`
   - Valor: começa com `VL_` ou contém `VALOR`
   - Status: começa com `ST_` (homologação e aptidão de pagamento são sub-casos: contém `HOMOLOG` / começa com `ST_APTO`)
   - Classificação/nota: contém `NOTA` ou `CLASSIFICACAO`
   - Quantidade: começa com `QT_`/`QTDE`

Colunas que não batem em nenhum padrão **não se perdem** — ficam em JSON (`valores_json`, `status_json`, `notas_json`, `quantidades_json`), consultáveis com `json_extract()` do SQLite.

### Armadilha já encontrada e corrigida (27/10/2026)

Um teste por substring simples (`"CNES" in nome_coluna`) pegava colunas que **contêm** "CNES" no nome mas **não são** o código do estabelecimento: `CO_TIPO_UNIDADE_CNES`, `CO_TIPO_EQUIPE_CNES`, `TP_EQUIPE_CNES` (código de tipo, ex. "1"/"2"), `DS_SUBTIPO_VALIDADO_CNES` (texto descritivo), `ST_ATIVO_CNES`, `QT_TOTAL_CNES_IAF`, `CO_CBO_CNES`. Quando uma dessas aparecia **antes** da coluna `CO_CNES` real no cabeçalho (casos confirmados: `eAPP/rel-validacao-eapp` e `Residentes/rel-validacao-residente`), o indexador pegava o valor errado como se fosse o CNES, inflando o ranking de valores para centenas de milhares/milhões de reais em "CNES" que na verdade eram descrições de modalidade ou códigos de tipo.

**Correção**: exigir que o nome da coluna **comece com `CO_CNES`** (não só contenha "CNES"). Isso cobre `CO_CNES`, `CO_CNES_ESB`, `CO_CNES_ESF_EAP`, `CO_CNES_VINCULO`, `CO_CNES_CREDENCIAMENTO`, `CO_CNES_VALIDACAO` — e exclui corretamente todas as armadilhas acima, que passam a cair nos buckets certos (`status`, `quantidade`, etc.).

**Regra para o futuro**: se um mês novo trouxer uma coluna de código do estabelecimento com nome fora do padrão `CO_CNES*`, o sintoma será um CNES "campeão" com valor claramente fora da escala normal (casas de milhões) ou um "CNES" que é texto/código pequeno em vez de um número de 7 dígitos. Validar sempre o top 1-2 do ranking contra um CNES já conferido manualmente antes de confiar no resultado de um mês novo.

---

## Limitações conhecidas dos dados (não é bug do indexador)

- **Nem todo arquivo de validação tem coluna de valor em R$.** Confirmado em 27/10/2026 para a Parcela_10_2026: `ACS` não tem nenhuma coluna `VL_`/`VALOR` — só flags de status (`ST_PAGAMENTO`). O valor por ACS precisa ser aplicado externamente (ver abaixo).
- **Os arquivos de validação geralmente só trazem o componente de "qualidade adicional" (`VL_PAGAMENTO_QUALIDADE`), não o custeio-base da equipe.** Exceção: `eAPP` tem `VL_PAGAMENTO` (valor cheio por equipe, varia por modalidade). Ou seja, "ranking de CNES por valor" soma o que existe nos arquivos — é uma visão parcial, mais completa para eAPP/eCR/eSB-qualidade, subestimada para eSF/eMulti/ACS (falta o custeio-base).
- **Homologação**: a coluna de homologação varia por estratégia (`ST_EQUIPE_HOMOLOGADA`, `ST_ESB_HOMOLOGADA`, etc.) — todas contêm `HOMOLOG` no nome, então caem no mesmo bucket, mas o significado de "uma linha" varia (ACS é por profissional vinculado, eSB é por equipe) — contar `DISTINCT equipe` ajuda mas não elimina essa diferença de granularidade entre categorias.

## Valores de referência manuais aprendidos (Parcela_10_2026, confirmados pelo usuário)

Para estimar o valor **completo** (custeio + qualidade) quando o arquivo de validação só traz a qualidade, os seguintes valores fixos foram confirmados manualmente, válidos para município com **IED 4** (ex.: São Paulo/SP):

| Estratégia | Custeio fixo | Qualidade (BOM) | Qualidade (ÓTIMO) | Vínculo/Território |
|---|---|---|---|---|
| eSF | R$ 12.000,00/equipe | R$ 6.000,00 | — | R$ 6.000,00/equipe |
| eMulti Complementar | R$ 24.000,00/equipe | — | R$ 6.000,00 | — |
| eSB 40h, Modalidade II | R$ 7.064,00/equipe | — | — | — |
| ACS | R$ 3.242,00/agente/mês (+ 13ª parcela anual) | — | — | — |

Esses valores **variam por IED** (índice de equidade e dimensionamento do município) — não assumir que valem para outro IED sem confirmar. Fonte: cálculo manual do usuário, validado cruzando com o CNES 2788381 (ver histórico de conversa de 27-28/10/2026).

---

## Arquivos relacionados

- `indexar_parcela.py` — script indexador (raiz do projeto, versionado no Git).
- `banco_parcelas.db` — banco SQLite gerado (raiz do projeto, **nunca versionar**, no `.gitignore`).
- Pasta fonte: `Y:\DESCO\COORDENAÇÕES\CGFAP\000 - PAGAMENTO\2026\Parcela_XX_2026\` (rede, fora do repositório).
