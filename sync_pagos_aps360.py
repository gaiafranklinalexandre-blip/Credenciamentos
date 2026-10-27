"""
Sincroniza os totais nacionais de equipes/servicos PAGOS (cofinanciados),
lidos do APS.xlsx do projeto APS 360, para o mesmo banco do painel de
Credenciamento/Represados (acao sync_pagos_estrategia no sync.php).

Rodar manualmente sempre que o APS.xlsx do APS 360 for atualizado (fluxo
mensal, junto da parcela nova):

    python sync_pagos_aps360.py

De-para de estrategia (represados) -> coluna "paga" no APS.xlsx acordado
com o usuario em 27/10/2026:
  - Conversoes (eSF>eSFR, eAP>eSF, eSB CHD>eSB 40h, Alteracao de
    tipologia eAPP) nao tem "pago" proprio: nao entram nesta sincronizacao.
    O painel calcula a projecao delas comparando com o pago da categoria
    de destino.
  - "Novo PAC Conectividade" nao tem mapeamento definido (usuario pediu
    para nao mapear) - fica de fora.
  - "Academia de Saude" = aba PAS (nao IAF).
  - "Inclusao de Incentivo Quilombola - eSF" = eSF/QUILOMBOLA_QNT_EQUIPE_CREDENCIADA
    (quantidade credenciada, nao "paga" - cada solicitacao e uma equipe nova).
  - "Atencao Primaria Prisional" ja soma eAPP Municipal + Estadual, cobrindo
    tambem o represado "eAPP Complementar Psicossocial" (sem coluna propria).
"""

import sys
import pandas as pd
import requests

APS_XLSX = r"C:\Users\franklin.santos\OneDrive - Ministério da Saúde\Área de Trabalho\APS 360\APS.xlsx"
API_URL = "https://darkgoldenrod-pelican-495804.hostingersite.com/sync.php"
API_KEY = "painel_cred_2026_key"


def soma_colunas(aba, colunas):
    df = pd.read_excel(APS_XLSX, sheet_name=aba, usecols=colunas, engine="openpyxl")
    total = 0.0
    for col in colunas:
        total += pd.to_numeric(df[col], errors="coerce").fillna(0).sum()
    return int(round(total))


def main():
    print(f"Lendo {APS_XLSX} ...")

    pagos = {}

    pagos["Saúde da Família"] = soma_colunas("eSF", ["Qtde. eSF Pagas"])
    pagos["Inclusão de Incentivo Quilombola - eSF"] = soma_colunas(
        "eSF", ["QUILOMBOLA_QNT_EQUIPE_CREDENCIADA"]
    )
    pagos["Atenção Primária (eAP)"] = soma_colunas("EAP", ["Qtde. eAP Pagas"])

    pagos["Saúde Bucal - 40 Horas"] = soma_colunas("ESB", ["ESB_40_PGO"])
    pagos["Saúde Bucal com carga horária diferenciada"] = soma_colunas("ESB", ["ESB_DIF_PGO"])

    pagos["eMulti Estratégica"] = soma_colunas("EMULTI", ["Qt. eMulti Estratégica Pagas"])
    pagos["eMulti Complementar"] = soma_colunas("EMULTI", ["Qt. eMulti Complementar Pagas"])
    pagos["eMulti Ampliada"] = soma_colunas("EMULTI", ["Qt. eMulti Ampliada Pagas"])
    pagos["eMulti Ampliada Intermunicipal"] = soma_colunas("EMULTI", ["Qt. eMulti Intermunicipal Pagas"])

    pagos["Saúde da Família Ribeirinha"] = soma_colunas("ESFR", ["EQUIPE_ESFR_QNT_PAGO"])

    pagos["Atenção Primária Prisional"] = soma_colunas("EAPP", ["Soma eAPP Est e Mum"])

    pagos["Consultório na Rua"] = soma_colunas(
        "ECR",
        [
            "Qtde. eCR Modalidade I Pagas",
            "Qtde. eCR Modalidade II Pagas",
            "Qtde. eCR Modalidade III Pagas",
        ],
    )

    pagos["Unidade Odontológica Móvel"] = soma_colunas("UOM", ["Qt. UOM Paga"])
    pagos["Academia de Saúde"] = soma_colunas("PAS", ["PAS Qtde. Estabelecimentos Pagos"])

    pagos["Pnaisari"] = soma_colunas("PNAISARI", ["QNT_EST_PNAISARI_PAGO"])

    pagos["Ampliação de eMulti - Padi"] = soma_colunas("PADI", ["Qtde. PADI Pago"])

    pagos["Agente Comunitário de Saúde"] = soma_colunas(
        "ACS", ["Qt. ACS (95% e 5%) Pago DIRETO"]
    ) + soma_colunas("ACS_IND", ["Qtde. ACS pago INDIRETO"])

    pagos["Incentivo para Residência na Atenção Primária à Saúde"] = soma_colunas(
        "RESIDENCIA", ["Total residentes pagos"]
    )

    print("\nTotais pagos (nacional):")
    for k, v in pagos.items():
        print(f"  {k}: {v:,}".replace(",", "."))

    registros = [{"estrategia": k, "quantidade_paga": v} for k, v in pagos.items()]

    print(f"\nEnviando {len(registros)} registros para {API_URL} ...")
    resp = requests.post(
        API_URL,
        params={"action": "sync_pagos_estrategia"},
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
        print(f"ERRO: nao encontrei o arquivo {APS_XLSX}")
        print("Confira se o OneDrive do APS 360 esta sincronizado neste computador.")
        sys.exit(1)
