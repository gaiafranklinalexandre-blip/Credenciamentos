# sync.php — Código para Represados

Este documento contém o código PHP que deve ser adicionado ao arquivo `sync.php` no Hostinger.

⚠️ **NOTA**: `sync.php` NÃO está no GitHub — é atualizado manualmente via painel Hostinger.

---

## 1. Criar Tabela `represados` (na seção de CREATE TABLE)

Adicionar após a criação da tabela `portarias`:

```php
// Criar tabela represados
$conn->query("CREATE TABLE IF NOT EXISTS represados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ibge INT,
    uf VARCHAR(5),
    municipio VARCHAR(100),
    nro_solicitacao VARCHAR(50),
    data_solicitacao DATE,
    estrategia VARCHAR(150),
    status VARCHAR(50),
    quantidade INT DEFAULT 0,
    request_type VARCHAR(20),
    obs TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ibge (ibge),
    INDEX idx_status (status),
    INDEX idx_estrategia (estrategia)
)");
```

---

## 2. Handler `sync_represados` (POST — para sync.py)

Adicionar na seção de tratamento de `$action`:

```php
elseif ($action === 'sync_represados') {
    // Requer autenticação
    $headers = getallheaders();
    $api_key = $headers['X-Api-Key'] ?? '';
    
    if ($api_key !== 'painel_cred_2026_key') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    
    if (!$data || !isset($data['records']) || !is_array($data['records'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }
    
    // Truncar tabela e inserir novos dados
    $conn->query("TRUNCATE TABLE represados");
    
    $stmt = $conn->prepare("INSERT INTO represados 
        (ibge, uf, municipio, nro_solicitacao, data_solicitacao, estrategia, status, quantidade, request_type, obs)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    
    $count = 0;
    foreach ($data['records'] as $r) {
        $ibge = (int)($r['ibge'] ?? 0);
        $uf = (string)($r['uf'] ?? '');
        $municipio = (string)($r['municipio'] ?? '');
        $nro = (string)($r['nro_solicitacao'] ?? '');
        $data_sol = $r['data_solicitacao'] ?: null;
        $est = (string)($r['estrategia'] ?? '');
        $stat = (string)($r['status'] ?? '');
        $qtd = (int)($r['quantidade'] ?? 0);
        $req_type = (string)($r['request_type'] ?? '');
        $obs = (string)($r['obs'] ?? '');
        
        $stmt->bind_param(
            'isssssssss',
            $ibge, $uf, $municipio, $nro, $data_sol, $est, $stat, $qtd, $req_type, $obs
        );
        
        if (!$stmt->execute()) {
            error_log("Error inserting represado: " . $stmt->error);
            continue;
        }
        $count++;
    }
    
    $stmt->close();
    http_response_code(200);
    echo json_encode(['success' => true, 'inserted' => $count]);
}
```

---

## 3. Handler `represados` (GET — para frontend)

Adicionar na seção de tratamento de `$action`:

```php
elseif ($action === 'represados') {
    // GET endpoint — sem autenticação (público, mas com rate limit se desejar)
    $result = $conn->query("SELECT * FROM represados ORDER BY data_solicitacao DESC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed']);
        exit;
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($rows);
}
```

---

## Como Adicionar ao sync.php no Hostinger

1. Acessar painel Hostinger → File Manager
2. Navegar até a pasta do site (raiz do domínio)
3. Editar arquivo `sync.php`
4. **Seção 1**: Adicionar CREATE TABLE dentro do bloco `if (!$conn->query("CREATE TABLE..."))` existente
5. **Seção 2 e 3**: Adicionar após o handler `sync_portarias` no grande `if/elseif` de actions
6. Salvar arquivo

---

## Teste de Funcionamento

### Teste 1: Sincronizar dados (sync.py local)

```bash
python sync.py
# Output esperado:
# Lendo base_credenciamentos_2026.xlsx...
# 12948 registros carregados.
# ...
# Lendo REPRESADO_2.xlsx...
# 17982 represados carregados.
# Enviando represados para o servidor...
# Represados sincronizados: 17982 registros.
```

### Teste 2: Verificar dados na tabela (phpmyadmin Hostinger)

```sql
SELECT COUNT(*) FROM represados;
-- Deve retornar: 17982

SELECT DISTINCT status FROM represados;
-- Deve retornar: SOLICITADA, CREDENCIADA, PUBLICADO EM PORTARIA
```

### Teste 3: Verificar GET endpoint

```bash
curl "https://darkgoldenrod-pelican-495804.hostingersite.com/sync.php?action=represados" | head -c 200
# Deve retornar JSON com array de represados
```

---

## Troubleshooting

| Erro | Causa | Solução |
|---|---|---|
| `Unauthorized` | Chave API incorreta | Verificar `painel_cred_2026_key` |
| `Invalid payload` | JSON malformado | Verificar estrutura em sync.py |
| `Prepare failed` | Erro SQL no INSERT | Verificar nomes de colunas |
| `Error inserting represado` | Dados faltando | Verificar parse em sync.py |

---

## Estrutura de Dados Inserida

Cada registro tem esta estrutura:

```json
{
  "ibge": 3500105,
  "uf": "SP",
  "municipio": "Adamantina",
  "nro_solicitacao": "AMPL.2026.000005833",
  "data_solicitacao": "2026-03-15",
  "estrategia": "eMulti Estratégica",
  "status": "SOLICITADA",
  "quantidade": 2,
  "request_type": "AMPL",
  "obs": "Padi e Atendimento Remoto"
}
```
