# Revisão gov.br DS — 22/11/2026

Preferência permanente do usuário: toda página, site ou painel novo/alterado, interno ou externo, deve seguir o gov.br DS, sem logo gov.br. Preservar a identidade SUS, os dados e as regras. Exceções explícitas: Modo Tático e todos os Totems. Consultar a documentação oficial e conferir teclado, foco, responsividade e contraste. Essa preferência não autoriza publicação permanente de tarefas futuras.

Filtros gerais dos painéis e de Valores CNES aprimorados com BRSelect 3.7.0, mantendo o select nativo como fonte de opções, eventos e FormData. Catálogos extensos usam busca no universo completo e exibem até 60 correspondências por vez; nenhuma opção é removida. Sem Core, permanece o campo nativo. O simulador conserva seus dois componentes próprios.

Rawline, foco, rótulos, tabelas em grade e detalhe financeiro foram conferidos. Wrapper com SUS e sem logo gov.br; destino Power BI preservado. O Modo Tático mantém os trechos CSS, HTML e JavaScript idênticos ao início desta revisão. Totems fora do escopo. Fonte comum e regra permanente no APS 360: `02_PAGINAS_ONLINE/design_referencia/govbr/` e `docs/regras/10-padrao-govbr.md`.

Verificações: dez larguras × quatro abas, contraste e dicas; simulador com mouse/teclado, limpeza, URL e datas; `tests/verificar-integracao-govbr.cjs` verifica filtro UF/URL, FormData, catálogo municipal completo, consulta e modal/Escape de Valores CNES em sete larguras. Resultado local: `tests/govbr-20261122-local.json`. O teste do simulador agora restringe a contagem a `#conteudoTab .br-select`, pois há também componentes nos filtros gerais.

Limites: não há certificação WCAG, revisão integral com leitor de tela ou zoom real. Represados completo depende da restrição por IP existente; não contornar autenticação. Não alterar APIs ou sincronizar dados nesta entrega.

Publicação dos frontends públicos autorizada pelo usuário nesta tarefa. Valores CNES permanece local/interno: banco, caches, planilhas e dados financeiros não acompanham o push. Mantidas parcela 10/2026, aptidão S, projeção de 12 parcelas e acumulado separados. Novas parcelas dependem de solicitação.
