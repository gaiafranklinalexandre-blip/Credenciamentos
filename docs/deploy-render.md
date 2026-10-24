# Deploy no Render

## Acompanhamento integrado — 24/10/2026

A consulta passa a reunir credenciamento e homologação, com prioridades, situação, saldo e orientação no próprio cartão. Dashboard e abas separadas foram removidos por solicitação do usuário. Portarias seguem acessíveis. Datas e quantitativos vêm exclusivamente das bases da API, sem fallback fixo. Regras, limites e validação atuais: [acompanhamento-integrado.md](acompanhamento-integrado.md). O teste vigente desta experiência é `tests/integrado.cjs`; instruções abaixo sobre dashboard e cinco abas descrevem a experiência anterior e não se aplicam à nova consulta.



## Identidade compartilhada — 24/10/2026

Frontend enviado ao repositório Credenciamentos/main no commit `9654b7a`. CSS, JS de navegação, consentimento e favicon ficam incorporados no index.html: não há dependências locais novas a enviar. Manter dados e backend separados; esta revisão não publica sync.php, sync.py ou planilhas.

**Endereços confirmados pelo usuário em 24/10/2026:** o frontend nativo deste projeto está em `https://credenciamentos.onrender.com/`. `https://painel-credenciamentos.onrender.com/` serve o wrapper antigo de Power BI, do repositório Page_credenciamento (ver wrapper-ga4.md), e não este frontend. Não confundir os dois produtos nem substituir o wrapper ao publicar este projeto. O HTML de credenciamentos.onrender.com retornou HTTP 200 e foi comparado ao index.html local, normalizando quebras de linha.

Validação: `node tests/visual_aps.cjs`, com Playwright disponível no ambiente, servidor HTTP local na porta 8963 ou `CRED_TEST_URL` para o Render. O teste usa dados públicos reais, abre Salvador pelo autocomplete, verifica cinco abas, CSV, filtros do dashboard, carregamento tardio de Chart.js/Leaflet, mapa e 21 combinações (sete larguras × três áreas). Testa ainda recusa/aceite/revogação de GA; a tag é interceptada no aceite para não coletar QA. Capturas e resumo ficam em validacao_visual/. Não confundir o site principal com o wrapper Power BI (`index-wrapper.html`) ou com represados.html.

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
5. Abrir o Dashboard — verificar KPIs, mapa e gráficos.
6. Verificar se a aba Legislação mostra as portarias.

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
