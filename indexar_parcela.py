"""
Indexador generico de parcelas de pagamento da APS (pasta CGFAP).

Le todos os .xlsx dentro de uma pasta Parcela_XX_AAAA (qualquer nivel de
subpasta) e consolida num banco SQLite local, para permitir perguntas
tipo "qual CNES recebeu mais nesta parcela" ou "quantas homologacoes
teve essa parcela" sem reabrir dezenas de planilhas gigantes toda vez.

IMPORTANTE — por que isso nao depende de nomes fixos:
  - A ESTRATEGIA de cada arquivo vem do nome da PASTA (ACS, eSF_eAP,
    eMulti, eSB, eCR, ...), nao do nome do arquivo nem de uma coluna.
    Pasta e o identificador mais estavel que existe nessas bases.
  - As colunas de interesse (CNES, equipe, UF, municipio, competencia,
    parcela, valores, status, nota/classificacao, quantidades) sao
    detectadas por PADRAO no nome (prefixo/substring), nao por nome
    exato. Isso sobrevive a troca de nome de coluna entre meses
    (ex.: CO_CNES_ESB virar CO_CNES_ESTABELECIMENTO continua sendo
    reconhecido, porque ambos contem "CNES").
  - Colunas que o padrao nao reconhece nao se perdem: ficam guardadas
    em JSON (extras_status, extras_valor, extras_outros), entao uma
    coluna nova nao quebra a importacao, so fica disponivel pra
    consulta manual via json_extract no SQLite.

Uso:
    python indexar_parcela.py "Y:\\DESCO\\COORDENAÇÕES\\CGFAP\\000 - PAGAMENTO\\2026\\Parcela_10_2026"

Gera (ou atualiza) banco_parcelas.db na pasta deste script, com uma
linha por registro de equipe/profissional/vinculo encontrado, mais
duas consultas de exemplo ao final (ranking de CNES por valor e
contagem de homologacoes).
"""

import json
import re
import sqlite3
import sys
from pathlib import Path

import openpyxl

DB_PATH = Path(__file__).parent / "banco_parcelas.db"


def normalizar(nome):
    return re.sub(r"[^A-Z0-9_]", "_", str(nome).upper())


def detectar_colunas(header):
    """Classifica cada coluna do cabecalho por padrao de nome, sem
    depender do nome exato. Retorna um dict de listas de indices."""
    cols = {
        "uf": [], "municipio_nome": [], "municipio_ibge": [],
        "competencia": [], "parcela": [], "cnes": [], "equipe": [],
        "valor": [], "status": [], "homolog": [], "apto_pagamento": [],
        "nota": [], "quantidade": [],
    }
    for i, h in enumerate(header):
        if h is None:
            continue
        n = normalizar(h)
        if n in ("SG_UF", "UF") or n.startswith("UF_"):
            cols["uf"].append(i)
        elif "MUNICIPIO" in n and ("IBGE" in n or n.startswith("CO_")):
            cols["municipio_ibge"].append(i)
        elif "MUNICIPIO" in n and (n.startswith("NO_") or "NOME" in n):
            cols["municipio_nome"].append(i)
        elif "COMP" in n and "CNES" in n:
            cols["competencia"].append(i)
        elif n.startswith("NU_COMP") or n == "COMPETENCIA":
            cols["competencia"].append(i)
        elif "PARCELA" in n and n.startswith(("NU_", "CO_")):
            cols["parcela"].append(i)
        elif n.startswith("CO_CNES"):
            # Estrito de proposito: CO_CNES, CO_CNES_ESB, CO_CNES_ESF_EAP,
            # CO_CNES_VINCULO, CO_CNES_CREDENCIAMENTO, CO_CNES_VALIDACAO sao
            # o codigo do estabelecimento. Colunas como CO_TIPO_UNIDADE_CNES,
            # CO_TIPO_EQUIPE_CNES, TP_EQUIPE_CNES, DS_SUBTIPO_VALIDADO_CNES,
            # ST_ATIVO_CNES, QT_TOTAL_CNES_IAF contem "CNES" mas NAO sao o
            # codigo (sao tipo/descricao/status/quantidade) - um teste por
            # substring pegaria essas por engano e inflaria os valores.
            cols["cnes"].append(i)
        elif n.startswith("CO_EQUIPE"):
            cols["equipe"].append(i)
        elif n.startswith("VL_") or "VALOR" in n:
            cols["valor"].append(i)
        elif "HOMOLOG" in n:
            cols["homolog"].append(i)
        elif n.startswith("ST_APTO"):
            cols["apto_pagamento"].append(i)
        elif n.startswith("ST_"):
            cols["status"].append(i)
        elif "NOTA" in n or "CLASSIFICACAO" in n:
            cols["nota"].append(i)
        elif n.startswith("QT_") or n.startswith("QTDE"):
            cols["quantidade"].append(i)
    return cols


def valor_num(v):
    try:
        f = float(v)
        return f if f != 0 else None
    except (TypeError, ValueError):
        return None


def indexar_arquivo(caminho, categoria, pasta_parcela, conn):
    wb = openpyxl.load_workbook(caminho, read_only=True, data_only=True)
    total_linhas = 0
    for aba in wb.sheetnames:
        ws = wb[aba]
        header = None
        cols = None
        linhas_buffer = []
        for i, row in enumerate(ws.iter_rows(values_only=True), start=1):
            if i == 1:
                header = list(row)
                cols = detectar_colunas(header)
                if not cols["cnes"] and not cols["equipe"]:
                    # aba sem nada reconhecivel (ex.: aba de notas/fontes) - pula
                    break
                continue

            def pega(idx_list, prefer_first_nonnull=True):
                for idx in idx_list:
                    if idx < len(row) and row[idx] not in (None, ""):
                        return row[idx]
                return None

            def pega_dict(idx_list):
                d = {}
                for idx in idx_list:
                    if idx < len(row):
                        v = row[idx]
                        if v not in (None, "", 0, 0.0):
                            d[str(header[idx])] = v
                return d

            cnes = pega(cols["cnes"])
            if cnes is None:
                continue
            equipe = pega(cols["equipe"])
            uf = pega(cols["uf"])
            municipio = pega(cols["municipio_nome"])
            ibge = pega(cols["municipio_ibge"])
            competencia = pega(cols["competencia"])
            parcela = pega(cols["parcela"])

            valores = pega_dict(cols["valor"])
            valor_total = sum(v for v in (valor_num(x) for x in valores.values()) if v is not None)

            status = pega_dict(cols["status"])
            homolog_vals = [row[idx] for idx in cols["homolog"] if idx < len(row) and row[idx] not in (None, "")]
            homologado = "S" if any(str(v).upper() == "S" for v in homolog_vals) else ("N" if homolog_vals else None)
            apto_vals = [row[idx] for idx in cols["apto_pagamento"] if idx < len(row) and row[idx] not in (None, "")]
            apto_pagamento = "S" if any(str(v).upper() == "S" for v in apto_vals) else ("N" if apto_vals else None)

            notas = pega_dict(cols["nota"])
            quantidades = pega_dict(cols["quantidade"])

            linhas_buffer.append((
                pasta_parcela, categoria, Path(caminho).name, aba,
                str(competencia) if competencia is not None else None,
                str(parcela) if parcela is not None else None,
                str(uf) if uf is not None else None,
                str(ibge) if ibge is not None else None,
                str(municipio) if municipio is not None else None,
                str(cnes).split(".")[0],
                str(equipe) if equipe is not None else None,
                valor_total if valor_total else None,
                json.dumps(valores, ensure_ascii=False) if valores else None,
                json.dumps(status, ensure_ascii=False) if status else None,
                homologado,
                apto_pagamento,
                json.dumps(notas, ensure_ascii=False) if notas else None,
                json.dumps(quantidades, ensure_ascii=False) if quantidades else None,
            ))
            total_linhas += 1

            if len(linhas_buffer) >= 5000:
                conn.executemany(INSERT_SQL, linhas_buffer)
                linhas_buffer = []

        if linhas_buffer:
            conn.executemany(INSERT_SQL, linhas_buffer)

    wb.close()
    return total_linhas


INSERT_SQL = """
INSERT INTO fatos (
    pasta_parcela, categoria, arquivo, aba, competencia, parcela, uf, ibge, municipio,
    cnes, equipe, valor_total, valores_json, status_json, homologado,
    apto_pagamento, notas_json, quantidades_json
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
"""

CREATE_SQL = """
CREATE TABLE IF NOT EXISTS fatos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pasta_parcela TEXT,
    categoria TEXT,
    arquivo TEXT,
    aba TEXT,
    competencia TEXT,
    parcela TEXT,
    uf TEXT,
    ibge TEXT,
    municipio TEXT,
    cnes TEXT,
    equipe TEXT,
    valor_total REAL,
    valores_json TEXT,
    status_json TEXT,
    homologado TEXT,
    apto_pagamento TEXT,
    notas_json TEXT,
    quantidades_json TEXT
)
"""


def main():
    if len(sys.argv) < 2:
        print("Uso: python indexar_parcela.py <caminho-da-pasta-Parcela_XX_AAAA>")
        sys.exit(1)

    pasta_raiz = Path(sys.argv[1])
    if not pasta_raiz.exists():
        print(f"Pasta nao encontrada: {pasta_raiz}")
        sys.exit(1)

    conn = sqlite3.connect(DB_PATH)
    conn.execute(CREATE_SQL)
    conn.execute("CREATE INDEX IF NOT EXISTS idx_cnes ON fatos(cnes)")
    conn.execute("CREATE INDEX IF NOT EXISTS idx_parcela ON fatos(parcela)")
    conn.execute("CREATE INDEX IF NOT EXISTS idx_categoria ON fatos(categoria)")
    conn.execute("CREATE INDEX IF NOT EXISTS idx_pasta_parcela ON fatos(pasta_parcela)")

    # Remove indexacao anterior desta MESMA pasta de parcela (reprocessamento
    # idempotente) sem apagar parcelas de outros meses ja indexadas antes -
    # o banco cresce mes a mes, permitindo comparar parcelas ao longo do tempo.
    nome_parcela = pasta_raiz.name
    conn.execute("DELETE FROM fatos WHERE pasta_parcela = ?", (nome_parcela,))
    conn.commit()

    arquivos = [f for f in pasta_raiz.rglob("*.xlsx") if not f.name.startswith("~$")]
    print(f"{len(arquivos)} arquivos .xlsx encontrados em {pasta_raiz}")

    for arq in arquivos:
        # categoria = primeira subpasta dentro da pasta da parcela
        try:
            categoria = arq.relative_to(pasta_raiz).parts[0]
        except ValueError:
            categoria = "raiz"
        print(f"  indexando [{categoria}] {arq.name} ...", end=" ", flush=True)
        try:
            n = indexar_arquivo(arq, categoria, nome_parcela, conn)
            conn.commit()
            print(f"{n} linhas")
        except Exception as e:
            print(f"ERRO: {e}")

    print(f"\nBanco salvo em: {DB_PATH}")

    # ─── Exemplos de consulta (escopo: a parcela que acabou de ser indexada) ──
    # Para comparar entre parcelas, tire o "WHERE pasta_parcela = ..." e
    # agrupe por pasta_parcela tambem.
    print(f"\n=== Exemplo 1: Top 10 CNES por valor total em {nome_parcela} ===")
    for row in conn.execute("""
        SELECT cnes, uf, municipio, SUM(valor_total) AS total
        FROM fatos
        WHERE valor_total IS NOT NULL AND pasta_parcela = ?
        GROUP BY cnes
        ORDER BY total DESC
        LIMIT 10
    """, (nome_parcela,)):
        print(f"  CNES {row[0]} ({row[2]}/{row[1]}): R$ {row[3]:,.2f}")

    print(f"\n=== Exemplo 2: Homologacoes por categoria em {nome_parcela} ===")
    for row in conn.execute("""
        SELECT categoria, COUNT(DISTINCT equipe) AS equipes_homologadas
        FROM fatos
        WHERE homologado = 'S' AND pasta_parcela = ?
        GROUP BY categoria
        ORDER BY equipes_homologadas DESC
    """, (nome_parcela,)):
        print(f"  {row[0]}: {row[1]} equipes homologadas")

    conn.close()


if __name__ == "__main__":
    main()
