"""
Sincroniza atributos estrategicos/territoriais por municipio (porte, IVS,
Amazonia Legal, fronteira, faixa quilombola, macrorregiao/regiao de saude),
lidos de base_filtro.csv, para o banco do painel de represados (acao
sync_municipios_filtro no sync.php).

Rodar manualmente sempre que base_filtro.csv for atualizado:

    python sync_base_filtro.py

Arquivo fonte: base_filtro.csv (raiz deste projeto), separado por ";",
codificado em cp1252 (Windows-1252/ANSI) — conferido em 27/10/2026.
"""

import csv
import sys
import requests

CSV_PATH = "base_filtro.csv"
API_URL = "https://darkgoldenrod-pelican-495804.hostingersite.com/sync.php"
API_KEY = "painel_cred_2026_key"


def main():
    print(f"Lendo {CSV_PATH} ...")
    with open(CSV_PATH, encoding="cp1252", newline="") as f:
        leitor = csv.DictReader(f, delimiter=";")
        linhas = list(leitor)
    print(f"{len(linhas)} municipios lidos.")

    registros = []
    for linha in linhas:
        ibge_7 = (linha.get("IBGE_7 DIGITOS") or "").strip()
        if not ibge_7:
            continue
        registros.append({
            "ibge": int(ibge_7),
            "uf": (linha.get("UF") or "").strip(),
            "municipio": (linha.get("MUNICÍPIO") or "").strip(),
            "regiao": (linha.get("REGIAO") or "").strip(),
            "macro": (linha.get("MACRO") or "").strip(),
            "regiao_saude": (linha.get("REGIAO_SAUDE") or "").strip(),
            "porte": (linha.get("Porte") or "").strip(),
            "ivs": (linha.get("IVS") or "").strip(),
            "fronteira": (linha.get("Fronteira") or "").strip(),
            "fronteira_arco": (linha.get("Fronteira-Arco") or "").strip(),
            "quilombola_faixa": (linha.get("QUILOMBOLA_FAIXA_TEXTO") or "").strip(),
            "amazonia_legal": (linha.get("AMAZONIA_LEGAL") or "").strip(),
        })

    print(f"Enviando {len(registros)} registros para {API_URL} ...")
    resp = requests.post(
        API_URL,
        params={"action": "sync_municipios_filtro"},
        headers={"X-Api-Key": API_KEY},
        json={"records": registros},
        timeout=60,
    )
    resp.raise_for_status()
    print(resp.json())


if __name__ == "__main__":
    try:
        main()
    except FileNotFoundError:
        print(f"ERRO: nao encontrei {CSV_PATH} na raiz do projeto.")
        sys.exit(1)
