# GitHub Flow

## Repositório

```
https://github.com/gaiafranklinalexandre-blip/Credenciamentos
Branch principal: main
```

O push para `main` dispara deploy automático no Render.

---

## Fluxo de trabalho

Este projeto usa fluxo direto em `main`. O Claude Code executa o deploy completo automaticamente ao final de cada alteração no `index.html`:

```
1. Claude Code edita index.html
2. git add index.html
3. git commit -m "feat/fix/style: descrição clara da mudança"
4. git push origin main
5. Render detecta o push → deploy automático em ~1-2 min
6. Acessar o site e confirmar que a mudança está funcionando
```

**O Claude Code faz os passos 2–4 automaticamente** ao concluir uma alteração. Não é necessário rodar manualmente, a menos que o Claude Code tenha sido interrompido antes do push.

### Verificar se o deploy foi concluído

Após o push, aguardar ~2 minutos e acessar o site. Se a mudança não aparecer:
- Forçar atualização no browser: `Ctrl + Shift + R`
- Verificar no painel do Render se o deploy concluiu sem erro

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

## Rollback — como desfazer quando algo dá errado

### Situação 1 — commit feito mas ainda NÃO publicado (antes do push)

```bash
git reset --soft HEAD~1
```

Desfaz o commit e mantém as alterações no arquivo para você corrigir.

---

### Situação 2 — push já feito, site quebrado (caso mais comum)

Identificar o hash do commit que quebrou:
```bash
git log --oneline -5
```

Exemplo de saída:
```
a822122 feat: filtros secundarios de portaria e estrategia  ← este quebrou
e4e21f1 docs: regra critica - jamais alterar estrutura da base
5737634 docs: adiciona documentação do Power BI
```

Reverter o commit problemático (cria um novo commit que desfaz as mudanças):
```bash
git revert a822122
git push origin main
```

O Render detecta o push e faz deploy da versão anterior automaticamente em ~1-2 min. **Este é o método seguro — não reescreve o histórico.**

---

### Situação 3 — voltar para uma versão específica conhecida (emergência)

Se precisar restaurar exatamente um commit anterior ignorando tudo o que veio depois:

```bash
git log --oneline -10          # anota o hash da versão boa
git checkout <hash> index.html # restaura só o arquivo
git commit -m "revert: restaura index.html para versão estável <hash>"
git push origin main
```

---

### Como pedir ao Claude Code para reverter

Basta dizer qual commit desfazer — o Claude Code executa o `git revert` e o `push` automaticamente:

> "Reverte o último commit" — desfaz o mais recente  
> "Reverte o commit a822122" — desfaz um específico pelo hash  
> "Volta para a versão de antes dos filtros secundários" — Claude identifica o commit pelo histórico
