# sync.php — Patch para Represados

Adicionar estas linhas ao seu `sync.php` **original** (que estava funcionando).

---

## 1. CREATE TABLE represados

Adicionar **após** a criação da tabela portarias:

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

## 2. Handler sync_represados (POST)

Adicionar **após** o handler `sync_portarias`:

```php
// ─── SYNC REPRESADOS (POST) ────────────────────────────
elseif ($action === 'sync_represados') {
    $headers = getallheaders();
    $api_key_header = $headers['X-Api-Key'] ?? '';

    if ($api_key_header !== 'painel_cred_2026_key') {
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

## 3. Handler represados (GET)

Adicionar **após** o handler `portarias`:

```php
// ─── GET REPRESADOS (GET) ──────────────────────────────
elseif ($action === 'represados') {
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

## Resumo

- **Seção 1**: Copiar código CREATE TABLE, adicionar onde as outras tabelas são criadas
- **Seção 2**: Copiar handler sync_represados, adicionar após sync_portarias
- **Seção 3**: Copiar handler represados, adicionar após handler portarias

Pronto! O sync.php continua funcionando com as novas funcionalidades.
