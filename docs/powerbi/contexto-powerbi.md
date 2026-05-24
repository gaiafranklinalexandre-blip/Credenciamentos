# Power BI — Contexto do Projeto

## Status

**Em uso ativo** — operado em paralelo ao site enquanto o site não estiver totalmente validado. Será descontinuado quando o site substituir todas as funcionalidades.

## Objetivo

Painel Power BI de monitoramento dos Novos Credenciamentos da APS. Antecede o site e serve de referência funcional para o desenvolvimento do substituto web.

## Arquivo

`novo_painel.pbix` — armazenado localmente. Não versionado no GitHub.

## Fonte de dados

Mesma base Excel do site:
- `base_credenciamentos_2026.xlsx` — aba `Base`
- Atualizado manualmente por Franklin até o dia 15 de cada mês.

## Relação com o site

| Funcionalidade | Power BI | Site |
|---|---|---|
| Consulta por município | ✅ | ✅ |
| Filtros por UF, Região, Portaria | ✅ | ✅ |
| Status de homologação (3 parcelas) | ✅ | ✅ |
| Dica de ferramenta com regras | ✅ em desenvolvimento | ✅ |
| Dashboard gerencial | ✅ | ✅ |
| Mapa por UF | ✅ | ✅ (Leaflet) |
| Acesso público sem login | ❌ | ✅ |
| Atualização automática via sync | ❌ | ✅ |

## Critério de descontinuação

O Power BI será substituído quando o site estiver **totalmente validado** pela equipe da SAPS. Pendente de definição formal dos critérios de validação.
