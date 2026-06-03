# Instruções de Deploy — sync.php no Hostinger

## Dois arquivos PHP no projeto — qual subir?

| Arquivo | Onde fica | O que é |
|---|---|---|
| `sync.php` (raiz) | Local + Hostinger | **Arquivo real** com credenciais do banco. É esse que sobe no Hostinger. |
| `docs/sync-php-completo.php` | GitHub | Cópia de referência sem credenciais reais. Só para documentação/versionamento. |

**Sempre suba o `sync.php` da raiz do projeto.** Nunca o `docs/sync-php-completo.php`.

---

## Passo a Passo

### 1. Localizar o arquivo correto
- O arquivo a subir é `sync.php` na **raiz** do projeto local

### 2. Acessar Hostinger
1. Acessar: https://hpanel.hostinger.com/
2. Login com suas credenciais
3. Selecionar o domínio: **darkgoldenrod-pelican-495804.hostingersite.com**
4. No menu esquerdo, clicar em **Files** → **File Manager**

### 3. Localizar sync.php
1. Na barra superior, localizar a pasta raiz do site (geralmente `/public_html/` ou `/`)
2. Procurar por `sync.php`
3. Se existir, **fazer backup** (copiar e renomear para `sync.php.backup`)

### 4. Upload do Novo Arquivo
1. Clicar em **Upload** (ou arrastar o arquivo)
2. Selecionar **`sync.php`** (da raiz do projeto local)
3. Confirmar que o arquivo no Hostinger ficou com o nome `sync.php`

### 5. Verificar Permissões
1. Clicar com botão direito em `sync.php`
2. Selecionar **Change Permissions**
3. Garantir que é **644** (ou 755 para PHP, conforme server requer)

---

## Verificação

### Teste 1: Verificar se o arquivo está online
```bash
curl -I "https://darkgoldenrod-pelican-495804.hostingersite.com/sync.php?action=data"
# Deve retornar HTTP 200 ou 400 (action sem POST)
```

### Teste 2: Rodar sync.py localmente
```bash
python sync.py
# Esperado:
# Lendo base_credenciamentos_2026.xlsx...
# 12948 registros carregados.
# Enviando para o servidor...
# Lote 1: 1000 registros inseridos
# ...
# Sincronização concluída! 12948 registros no banco.
# Lendo Portarias_APS_Database.xlsx...
# X portarias carregadas.
# Portarias sincronizadas: X registros.
# Lendo REPRESADO_2.xlsx...
# 17982 represados carregados.
# Enviando represados para o servidor...
# Represados sincronizados: 17982 registros.
```

### Teste 3: Verificar Dados no Servidor
```bash
curl "https://darkgoldenrod-pelican-495804.hostingersite.com/sync.php?action=data" | head -c 200
# Deve retornar JSON com credenciamentos

curl "https://darkgoldenrod-pelican-495804.hostingersite.com/sync.php?action=portarias" | head -c 200
# Deve retornar JSON com portarias

curl "https://darkgoldenrod-pelican-495804.hostingersite.com/sync.php?action=represados" | head -c 200
# Deve retornar JSON com represados
```

### Teste 4: Acessar Dashboard
- Painel Credenciamentos: https://credenciamentos.onrender.com
- Painel Represados: https://credenciamentos.onrender.com/represados.html
  - Senha: `password` (para teste)

---

## Configurações do Arquivo

Caso o servidor do Hostinger tenha configurações diferentes, editar o início do `sync.php`:

```php
$db_host = 'localhost';     // Verificar com suporte Hostinger
$db_user = 'root';          // Seu usuário MySQL
$db_pass = '';              // Sua senha MySQL
$db_name = 'credenciamentos_aps';  // Nome do banco
$api_key = 'painel_cred_2026_key'; // Chave de autenticação
```

---

## Troubleshooting

| Erro | Solução |
|---|---|
| 403 Forbidden | Permissões incorretas (tentar 755 em vez de 644) |
| 500 Internal Server Error | Verificar logs do Hostinger; pode ser erro na conexão MySQL |
| MySQL connection failed | Verificar credenciais ($db_user, $db_pass, $db_name) |
| "API key mismatch" | Verificar se `X-Api-Key` no sync.py bate com `$api_key` no sync.php |
| Tabelas vazias após sync | Rodar `python sync.py` novamente |

---

## Próximos Passos

1. ✅ Upload de `sync.php` para Hostinger
2. ✅ Rodar `python sync.py`
3. ✅ Testar endpoints GET (`?action=data`, `?action=portarias`, `?action=represados`)
4. ✅ Acessar dashboards e validar dados

Pronto! 🚀
