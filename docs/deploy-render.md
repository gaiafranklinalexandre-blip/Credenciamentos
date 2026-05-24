# Deploy no Render

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
- URL: gerada automaticamente pelo Render (formato `https://xxx.onrender.com`)

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
