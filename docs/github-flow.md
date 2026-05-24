# GitHub Flow

## Repositório

```
https://github.com/gaiafranklinalexandre-blip/Credenciamentos
Branch principal: main
```

O push para `main` dispara deploy automático no Render.

---

## Fluxo de trabalho

Este projeto usa fluxo direto em `main` — sem branches de feature por enquanto:

```
1. Editar index.html (ou sync.py) localmente
2. git add index.html
3. git commit -m "mensagem"
4. git push origin main
5. Render detecta o push e faz deploy automático (~1-2 min)
```

---

## Padrão de commits

Usar prefixos semânticos:

| Prefixo | Quando usar |
|---|---|
| `feat:` | Nova funcionalidade |
| `fix:` | Correção de bug |
| `refactor:` | Reestruturação sem mudança de comportamento |
| `style:` | Mudança visual sem lógica |
| `docs:` | Documentação |
| `chore:` | Manutenção, configuração |

**Exemplos corretos:**
```
feat: adiciona Dashboard com KPIs, gráficos Chart.js e portarias ativas
fix: dashboard mostra apenas ativos + adiciona mapa Leaflet por UF
refactor: remove filtros por portaria do dashboard
fix: corrige bind_param no sync.php — 7 params isissss
```

**Exemplos a evitar:**
```
update
ajustes
correção
wip
```

---

## Arquivos que NÃO devem ser versionados

| Arquivo | Motivo |
|---|---|
| `base_credenciamentos_2026.xlsx` | Dados sensíveis, pesados |
| `Portarias_APS_Database.xlsx` | Dados sensíveis, pesados |
| `~$base_credenciamentos_2026.xlsx` | Arquivo temporário do Excel |
| `desktop.ini` | Configuração de pasta Windows |
| `novo_painel.pbix` | Arquivo Power BI, binário pesado |
| `simulador.xlsx` | Arquivo de trabalho local |
| `sync.php` | Não vai para o GitHub — atualizado manualmente no Hostinger |

Verificar antes de `git add`:
```bash
git status
```
Nunca usar `git add .` ou `git add -A` sem revisar o status antes.

---

## Cuidados antes do push

- [ ] Testar o `index.html` localmente (abrir no browser) antes de commitar.
- [ ] Verificar se não há erros de JavaScript no console.
- [ ] Confirmar que apenas `index.html` (e/ou `sync.py`, `CLAUDE.md`, `docs/`) estão no commit.
- [ ] Nunca commitar arquivos `.xlsx`, `.pbix`, `desktop.ini`, `~$*`.
- [ ] `sync.php` nunca vai para o GitHub.

---

## Co-autoria nos commits

Sempre incluir ao final da mensagem de commit:

```
Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
```

---

## Rollback

Para desfazer o último commit (antes do push):
```bash
git reset --soft HEAD~1
```

Para reverter um commit já publicado:
```bash
git revert <hash>
git push origin main
```
O Render fará deploy da versão revertida automaticamente.
