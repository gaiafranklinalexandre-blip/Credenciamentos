<?php
header('Content-Type: application/json');

// ─── CONFIGURAÇÃO ──────────────────────────────────────
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'credenciamentos_aps';
$api_key = 'painel_cred_2026_key';

// Conectar ao banco de dados
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$conn->set_charset('utf8mb4');

// ─── CRIAR TABELAS ─────────────────────────────────────

// Tabela credenciamentos
$conn->query("CREATE TABLE IF NOT EXISTS credenciamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uf VARCHAR(5),
    ibge INT,
    ibge_validacao INT,
    uf_validacao VARCHAR(5),
    municipio VARCHAR(100),
    regiao VARCHAR(100),
    macrorregiao VARCHAR(100),
    regiao_saude VARCHAR(100),
    ied INT,
    tipo VARCHAR(50),
    estrategia VARCHAR(100),
    nome_completo VARCHAR(200),
    portaria INT,
    data_portaria DATE,
    credenciado INT DEFAULT 0,
    homologacao_1 INT DEFAULT 0,
    homologacao_2 INT DEFAULT 0,
    homologacao_3 INT DEFAULT 0,
    impacto_1 INT DEFAULT 0,
    impacto_2 INT DEFAULT 0,
    selecao_credenciamento VARCHAR(50),
    selecao_homologacao VARCHAR(50),
    observacao TEXT,
    mensagem_homologacao TEXT,
    mensagem_painel TEXT,
    ano INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_municipio (municipio),
    INDEX idx_portaria (portaria),
    INDEX idx_selecao (selecao_credenciamento)
)");

// Tabela portarias
$conn->query("CREATE TABLE IF NOT EXISTS portarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    n_portaria INT UNIQUE,
    data_portaria DATE,
    ano INT,
    tipo_ato VARCHAR(100),
    equipe_servico VARCHAR(100),
    link TEXT,
    descricao TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_portaria (n_portaria),
    INDEX idx_ano (ano)
)");

// Tabela represados (NOVO)
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

// ─── HANDLERS ──────────────────────────────────────────

$action = $_GET['action'] ?? '';

// ─── SYNC CREDENCIAMENTOS (POST) ───────────────────────
if ($action === 'sync') {
    $headers = getallheaders();
    $api_key_header = $headers['X-Api-Key'] ?? '';

    if ($api_key_header !== $api_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!$data || !isset($data['records'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }

    $conn->query("TRUNCATE TABLE credenciamentos");

    $stmt = $conn->prepare("INSERT INTO credenciamentos
        (uf, ibge, ibge_validacao, uf_validacao, municipio, regiao, macrorregiao,
         regiao_saude, ied, tipo, estrategia, nome_completo, portaria, data_portaria,
         credenciado, homologacao_1, homologacao_2, homologacao_3, impacto_1, impacto_2,
         selecao_credenciamento, selecao_homologacao, observacao, mensagem_homologacao,
         mensagem_painel, ano)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $count = 0;
    foreach ($data['records'] as $r) {
        $uf = $r['uf'] ?? '';
        $ibge = (int)($r['ibge'] ?? 0);
        $ibge_val = (int)($r['ibge_validacao'] ?? 0);
        $uf_val = $r['uf_validacao'] ?? '';
        $mun = $r['municipio'] ?? '';
        $reg = $r['regiao'] ?? '';
        $macro = $r['macrorregiao'] ?? '';
        $reg_saude = $r['regiao_saude'] ?? '';
        $ied = (int)($r['ied'] ?? 0);
        $tipo = $r['tipo'] ?? '';
        $est = $r['estrategia'] ?? '';
        $nome = $r['nome_completo'] ?? '';
        $port = (int)($r['portaria'] ?? 0);
        $data_port = $r['data_portaria'] ?: null;
        $cred = (int)($r['credenciado'] ?? 0);
        $hom1 = (int)($r['homologacao_1'] ?? 0);
        $hom2 = (int)($r['homologacao_2'] ?? 0);
        $hom3 = (int)($r['homologacao_3'] ?? 0);
        $imp1 = (int)($r['impacto_1'] ?? 0);
        $imp2 = (int)($r['impacto_2'] ?? 0);
        $sel_cred = $r['selecao_credenciamento'] ?? '';
        $sel_hom = $r['selecao_homologacao'] ?? '';
        $obs = $r['observacao'] ?? '';
        $msg_hom = $r['mensagem_homologacao'] ?? '';
        $msg_painel = $r['mensagem_painel'] ?? '';
        $ano = (int)($r['ano'] ?? 0);

        $stmt->bind_param(
            'siissssssisssiiiiissssssi',
            $uf, $ibge, $ibge_val, $uf_val, $mun, $reg, $macro, $reg_saude,
            $ied, $tipo, $est, $nome, $port, $data_port, $cred, $hom1, $hom2, $hom3,
            $imp1, $imp2, $sel_cred, $sel_hom, $obs, $msg_hom, $msg_painel, $ano
        );

        if (!$stmt->execute()) {
            error_log("Error inserting credenciamento: " . $stmt->error);
            continue;
        }
        $count++;
    }

    $stmt->close();
    http_response_code(200);
    echo json_encode(['success' => true, 'inserted' => $count]);
}

// ─── APPEND CREDENCIAMENTOS (POST) ─────────────────────
elseif ($action === 'append') {
    $headers = getallheaders();
    $api_key_header = $headers['X-Api-Key'] ?? '';

    if ($api_key_header !== $api_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!$data || !isset($data['records'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO credenciamentos
        (uf, ibge, ibge_validacao, uf_validacao, municipio, regiao, macrorregiao,
         regiao_saude, ied, tipo, estrategia, nome_completo, portaria, data_portaria,
         credenciado, homologacao_1, homologacao_2, homologacao_3, impacto_1, impacto_2,
         selecao_credenciamento, selecao_homologacao, observacao, mensagem_homologacao,
         mensagem_painel, ano)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $count = 0;
    foreach ($data['records'] as $r) {
        $uf = $r['uf'] ?? '';
        $ibge = (int)($r['ibge'] ?? 0);
        $ibge_val = (int)($r['ibge_validacao'] ?? 0);
        $uf_val = $r['uf_validacao'] ?? '';
        $mun = $r['municipio'] ?? '';
        $reg = $r['regiao'] ?? '';
        $macro = $r['macrorregiao'] ?? '';
        $reg_saude = $r['regiao_saude'] ?? '';
        $ied = (int)($r['ied'] ?? 0);
        $tipo = $r['tipo'] ?? '';
        $est = $r['estrategia'] ?? '';
        $nome = $r['nome_completo'] ?? '';
        $port = (int)($r['portaria'] ?? 0);
        $data_port = $r['data_portaria'] ?: null;
        $cred = (int)($r['credenciado'] ?? 0);
        $hom1 = (int)($r['homologacao_1'] ?? 0);
        $hom2 = (int)($r['homologacao_2'] ?? 0);
        $hom3 = (int)($r['homologacao_3'] ?? 0);
        $imp1 = (int)($r['impacto_1'] ?? 0);
        $imp2 = (int)($r['impacto_2'] ?? 0);
        $sel_cred = $r['selecao_credenciamento'] ?? '';
        $sel_hom = $r['selecao_homologacao'] ?? '';
        $obs = $r['observacao'] ?? '';
        $msg_hom = $r['mensagem_homologacao'] ?? '';
        $msg_painel = $r['mensagem_painel'] ?? '';
        $ano = (int)($r['ano'] ?? 0);

        $stmt->bind_param(
            'siissssssisssiiiiissssssi',
            $uf, $ibge, $ibge_val, $uf_val, $mun, $reg, $macro, $reg_saude,
            $ied, $tipo, $est, $nome, $port, $data_port, $cred, $hom1, $hom2, $hom3,
            $imp1, $imp2, $sel_cred, $sel_hom, $obs, $msg_hom, $msg_painel, $ano
        );

        if (!$stmt->execute()) {
            error_log("Error inserting credenciamento: " . $stmt->error);
            continue;
        }
        $count++;
    }

    $stmt->close();
    http_response_code(200);
    echo json_encode(['success' => true, 'inserted' => $count]);
}

// ─── SYNC PORTARIAS (POST) ─────────────────────────────
elseif ($action === 'sync_portarias') {
    $headers = getallheaders();
    $api_key_header = $headers['X-Api-Key'] ?? '';

    if ($api_key_header !== $api_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!$data || !isset($data['records'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }

    $conn->query("TRUNCATE TABLE portarias");

    $stmt = $conn->prepare("INSERT INTO portarias (n_portaria, data_portaria, ano, tipo_ato, equipe_servico, link, descricao)
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $count = 0;
    foreach ($data['records'] as $r) {
        $nport = (int)($r['n_portaria'] ?? 0);
        $dport = $r['data_portaria'] ?: null;
        $ano = (int)($r['ano'] ?? 0);
        $tato = $r['tipo_ato'] ?? '';
        $equip = $r['equipe_servico'] ?? '';
        $link = $r['link'] ?? '';
        $desc = $r['descricao'] ?? '';

        $stmt->bind_param('isisss', $nport, $dport, $ano, $tato, $equip, $link, $desc);

        if (!$stmt->execute()) {
            error_log("Error inserting portaria: " . $stmt->error);
            continue;
        }
        $count++;
    }

    $stmt->close();
    http_response_code(200);
    echo json_encode(['success' => true, 'inserted' => $count]);
}

// ─── SYNC REPRESADOS (POST) ────────────────────────────
elseif ($action === 'sync_represados') {
    $headers = getallheaders();
    $api_key_header = $headers['X-Api-Key'] ?? '';

    if ($api_key_header !== $api_key) {
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
        $mun = (string)($r['municipio'] ?? '');
        $nro = (string)($r['nro_solicitacao'] ?? '');
        $data_sol = $r['data_solicitacao'] ?: null;
        $est = (string)($r['estrategia'] ?? '');
        $stat = (string)($r['status'] ?? '');
        $qtd = (int)($r['quantidade'] ?? 0);
        $req_type = (string)($r['request_type'] ?? '');
        $obs = (string)($r['obs'] ?? '');

        $stmt->bind_param(
            'isssssssss',
            $ibge, $uf, $mun, $nro, $data_sol, $est, $stat, $qtd, $req_type, $obs
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

// ─── GET DATA (GET) ────────────────────────────────────
elseif ($action === 'data') {
    $result = $conn->query("SELECT * FROM credenciamentos ORDER BY data_portaria DESC");

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

// ─── GET PORTARIAS (GET) ───────────────────────────────
elseif ($action === 'portarias') {
    $result = $conn->query("SELECT * FROM portarias ORDER BY data_portaria DESC");

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

// ─── GET REPRESADOS (GET) — restrito à rede do MS ─────
elseif ($action === 'represados') {
    // Verifica se o IP do cliente está na faixa autorizada do Ministério da Saúde
    $client_ip = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $client_ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }

    $redes_autorizadas = [
        '189.9.35.0/24',   // Rede MS — adicione outros blocos aqui se necessário
    ];

    function ip_em_rede($ip, $cidr) {
        list($subnet, $bits) = explode('/', $cidr);
        $mask = ~((1 << (32 - (int)$bits)) - 1);
        return (ip2long($ip) & $mask) === (ip2long($subnet) & $mask);
    }

    $autorizado = false;
    foreach ($redes_autorizadas as $rede) {
        if (ip_em_rede($client_ip, $rede)) { $autorizado = true; break; }
    }

    if (!$autorizado) {
        http_response_code(403);
        echo json_encode(['erro' => 'Acesso restrito à rede do Ministério da Saúde']);
        exit;
    }

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

// ─── DEFAULT ───────────────────────────────────────────
else {
    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
}

$conn->close();
?>
