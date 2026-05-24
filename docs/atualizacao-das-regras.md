# Atualização das Regras

## Quando atualizar

Atualizar a documentação sempre que ocorrer:

| Evento | Documentos impactados |
|---|---|
| Nova coluna na base Excel | `gestao-dados.md`, `CLAUDE.md` |
| Novo campo no MySQL (`sync.php`) | `gestao-dados.md` |
| Nova funcionalidade no front | `contexto-do-projeto.md`, `CLAUDE.md` |
| Mudança de regra de negócio (dias, offsets) | `gestao-dados.md`, `CLAUDE.md` |
| Mudança visual (cor, componente) | `design.md` |
| Mudança no fluxo de deploy | `deploy-render.md`, `github-flow.md` |
| Novo padrão de commit | `github-flow.md` |
| Erro recorrente resolvido | Documento do tema + `CLAUDE.md` |
| Dependência externa adicionada | `contexto-do-projeto.md`, `CLAUDE.md` |

---

## Como revisar o contexto antes de atualizar

1. Ler o `CLAUDE.md` para entender o estado atual.
2. Verificar o `git log --oneline -10` para identificar mudanças recentes.
3. Ler o documento específico da área afetada em `docs/`.
4. Comparar com o código atual em `index.html` ou `sync.py`.
5. Identificar o que mudou e o que ficou desatualizado.

---

## Como decidir entre criar nova regra ou atualizar existente

**Atualizar documento existente quando:**
- A regra já existe mas ficou desatualizada.
- O tema já está coberto no documento, só o detalhe mudou.
- É uma correção de informação incorreta.

**Criar novo documento quando:**
- O tema é claramente distinto dos existentes.
- O documento ficaria muito longo com a adição.
- É uma área nova do projeto sem cobertura.

**Nunca:**
- Duplicar a mesma regra em dois documentos.
- Criar regra genérica que não se aplica a este projeto especificamente.
- Deixar regras conflitantes coexistindo.

---

## Como evitar documentação duplicada

Antes de escrever qualquer regra nova:
```
1. Buscar o termo nos arquivos existentes (Grep no VS Code ou Claude Code).
2. Se encontrar, atualizar o trecho existente.
3. Se não encontrar, criar no documento mais adequado.
4. Registrar no CLAUDE.md apenas um link ou resumo de uma linha.
```

---

## Checklist de atualização

Ao fazer qualquer atualização na documentação:

- [ ] A mudança reflete o código atual (não o que "deveria ser")?
- [ ] Existe algum documento em `docs/` que contradiz a atualização?
- [ ] O `CLAUDE.md` precisa ser atualizado (mudança estrutural)?
- [ ] Existe regra duplicada que deve ser removida?
- [ ] A linguagem está objetiva e técnica (sem genéricos)?
- [ ] Exemplos concretos foram usados (não abstratos)?

---

## Erros já ocorridos — registrar para não repetir

| Erro | Causa | Prevenção |
|---|---|---|
| `sync.php` com bind_param errado | Contar chars do formato vs. número de params | Sempre contar: 1 char por param, verificar tipo (i=int, s=string) |
| Autocomplete sobrepondo filtros | z-index conflitante | `.capa-filtros` z-index: 0, dropdown z-index: 500 |
| Autocomplete vazio no carregamento | `dados` ainda vazio | Checar `if (!dados.length)` e re-trigger após load |
| `FORMAT` com texto dentro de aspas simples no DAX | Power BI interpreta como formato de data | Concatenar texto fora do FORMAT: `& " de " &` |
| Dashboard mostrando todos os registros | Faltou filtrar `selecao_credenciamento = 'Ativo'` | Base do dashboard sempre via `baseAtivos` |
| `sync.php` não atualizado no servidor | Arquivo está no Hostinger, não no GitHub | Atualizar manualmente via painel Hostinger após mudanças |
