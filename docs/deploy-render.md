# Deploy no Render

## Cards e parcelas — 25/10/2026

Revisão vigente: cada estratégia tem card com quantitativos rotulados, orientação municipal e resultados por parcela em tabela de três colunas. Não há cabeçalho de colunas flutuante. A parcela do mês é estática; somente ação municipal com prazo aberto pode pulsar. A espera futura é Aguardando resultado de [mês/ano], sem processamento confirmado inferido. Específicos continua somente credenciamento. Explicar narra os resultados do caso. Executar tests/navegacao.cjs localmente e no Render, conferir HTML servido e capturas desktop/mobile. A revisão altera somente frontend, testes e documentação; manter planilhas e backend preexistentes.

## Panorama e estados independentes — 24/10/2026

A navegação usa menu lateral (gaveta no celular), com Credenciamentos ativos como área padrão, além de Encerrados e Portarias; as homologações estão integradas, sem área própria. A busca inicial foi removida. O botão Filtros abre diálogo lateral com Região, UF, Município, Portaria, Estratégia. Aplicar confirma o rascunho; fechar descarta. Os filtros aplicados aparecem em chips removíveis. O detalhe municipal mostra o caminho Consolidado → Município / UF e o retorno explícito no cabeçalho fixo. Voltar restaura contexto/paginação; a troca de área preserva filtros. O histórico do navegador e o recarregamento preservam a consulta. O botão da linha do tempo se chama Explicar. Após Saldo, a tabela e o detalhe municipal exibem % de implantação (homologadas ÷ credenciadas × 100); no consolidado, usa-se a razão entre somas. Permanecem o consolidado por portaria/estratégia, apenas o KPI de ativos, conclusão abreviada e regras U/V. Referência vigente: [acompanhamento-integrado.md](acompanhamento-integrado.md). Teste: `tests/navegacao.cjs`. Esta revisão não altera backend ou bases. Descrições posteriores de cinco abas e dashboard são históricas.


## Identidade compartilhada — 24/10/2026

Frontend enviado ao repositório Credenciamentos/main no commit `9654b7a`. CSS, JS de navegação, consentimento e favicon ficam incorporados no index.html: não há dependências locais novas a enviar. Manter dados e backend separados; esta revisão não publica sync.php, sync.py ou planilhas.

**Endereços confirmados pelo usuário em 24/10/2026:** o frontend nativo deste projeto está em `https://credenciamentos.onrender.com/`. `https://painel-credenciamentos.onrender.com/` serve o wrapper antigo de Power BI, do repositório Page_credenciamento (ver wrapper-ga4.md), e não este frontend. Não confundir os dois produtos nem substituir o wrapper ao publicar este projeto. O HTML de credenciamentos.onrender.com retornou HTTP 200 e foi comparado ao index.html local, normalizando quebras de linha.

Validação vigente: `node tests/navegacao.cjs` (ou a entrada compatível `tests/visual_aps.cjs`), servidor local na porta 8964 e Playwright disponível por `CRED_PLAYWRIGHT`. `CRED_TEST_URL` permite validar o Render. Verifica navegação, filtros, retorno, consolidação, percentual de implantação, linha do tempo, CSV e seis larguras. Analytics permanece recusado durante esta validação; ela não comprova recebimento de eventos no GA4. Capturas ficam em `validacao_visual/`.

O checkout tem alterações preexistentes em sync.php/sync.py e documentos de dados, além de arquivos locais não versionados. Adicionar ao commit somente index.html, documentação visual/deploy/contexto e o teste desta revisão. Nunca usar git add . nesta entrega. A conferência final deve comparar o HTML servido com o local e repetir o teste no Render.

## Fluxo de deploy

O deploy é **automático** — não há nenhuma ação manual necessária além do push:

```
git push origin main
  ↓
GitHub detecta push
  ↓
Render detecta mudança no repositório
  ↓
Build (~30s) + deploy (~30s)
  ↓
Site atualizado (~1-2 min após o push)
```

## Configuração do Render

- Tipo: **Static Site**
- Branch: `main`
- Diretório de build: raiz do repositório
- Arquivo servido: `index.html`
- URL do frontend nativo: `https://credenciamentos.onrender.com/`.

---

## Validação pós-deploy

Após o push, aguardar ~2 minutos e verificar:

1. Abrir a URL do painel no browser.
2. Verificar se os dados carregam (filtros populados, autocomplete funcionando).
3. Abrir o console do browser — não deve haver erros JS.
4. Testar a busca por município.
5. Conferir o KPI de ativos, o consolidado e o percentual de implantação.
6. Conferir Encerrados, Portarias e a linha do tempo no detalhe municipal.

---

## Como verificar erros

### No browser
- Abrir DevTools (`F12`) → aba Console.
- Erros de JS aparecem em vermelho.
- Erros de fetch aparecem como `Failed to load resource`.

### No Render
- Acessar o painel do Render → serviço → aba **Logs**.
- Logs de build mostram se houve erro na construção.
- Logs de runtime mostram requisições recebidas.

### Na API (Hostinger)
- Acessar `https://darkgoldenrod-pelican-495804.hostingersite.com/sync.php?action=data` no browser.
- Deve retornar JSON com array de registros.
- Se retornar erro de DB, verificar credenciais no `sync.php`.

---

## Problemas comuns e soluções

| Problema | Causa provável | Solução |
|---|---|---|
| Site não atualiza após push | Deploy ainda em andamento | Aguardar 2 min, verificar logs no Render |
| Dados não carregam | API do Hostinger fora do ar | Verificar `sync.php?action=data` diretamente |
| Mapa não aparece | CDN do Leaflet indisponível | Verificar conexão, aguardar ou hospedar localmente |
| Gráficos não aparecem | CDN do Chart.js indisponível | Mesmo que o mapa |
| Autocomplete vazio | Dados ainda carregando | Aguardar — exibe "⏳ Carregando dados..." |
| CORS error no console | Hostinger com CORS bloqueado | Verificar headers no `sync.php` |

---

## Rollback

Para voltar a uma versão anterior:

```bash
# Opção 1: reverter commit específico (recomendado)
git revert <hash-do-commit>
git push origin main

# Opção 2: pelo painel do Render
# Render → serviço → Deploys → selecionar deploy anterior → Redeploy
```

A opção 2 é mais rápida mas não cria histórico no Git.

---

## Importante: sync.php não está no Render

O `sync.php` está hospedado no **Hostinger**, não no Render. São dois serviços separados:

| Serviço | Hospeda | Atualização |
|---|---|---|
| Render | `index.html` (frontend) | Automática via GitHub |
| Hostinger | `sync.php` + MySQL | Manual via painel Hostinger |

Para atualizar o `sync.php`:
1. Acessar o painel do Hostinger.
2. Ir em Gerenciador de Arquivos.
3. Navegar até a raiz do site.
4. Fazer upload do `sync.php` atualizado (substituir o existente).
