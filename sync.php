<?php
// Headers CORS — permite que o painel no Render acesse esta API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-Api-Key, Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// Chave de segurança - o Python deve enviar essa chave em cada requisição
define('API_KEY', 'painel_cred_2026_key');

// Configurações do banco
define('DB_HOST', 'localhost');
define('DB_USER', 'u127731061_0swzh');
define('DB_PASS', 'fEfK.+blBs7K6U()');
define('DB_NAME', 'u127731061_Zpe5T');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Ações de escrita exigem chave — leitura é pública
if (in_array($action, ['sync', 'append', 'sync_portarias', 'sync_represados'])) {
    $headers = getallheaders();
    if (!isset($headers['X-Api-Key']) || $headers['X-Api-Key'] !== API_KEY) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset('utf8mb4');

// Cria tabelas se não existirem
$conn->query("CREATE TABLE IF NOT EXISTS portarias_aps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    n_portaria INT,
    data_portaria DATE,
    ano INT,
    tipo_ato VARCHAR(100),
    equipe_servico VARCHAR(100),
    link TEXT,
    descricao TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS credenciamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uf VARCHAR(5),
    ibge INT,
    ibge_validacao INT,
    uf_validacao VARCHAR(5),
    municipio VARCHAR(100),
    regiao VARCHAR(50),
    macrorregiao VARCHAR(100),
    regiao_saude VARCHAR(100),
    ied INT,
    tipo VARCHAR(100),
    estrategia VARCHAR(100),
    nome_completo VARCHAR(200),
    portaria INT,
    data_portaria DATE,
    credenciado INT,
    homologacao_1 INT,
    homologacao_2 INT,
    homologacao_3 INT,
    impacto_1 INT,
    impacto_2 INT,
    selecao_credenciamento VARCHAR(100),
    selecao_homologacao VARCHAR(100),
    observacao TEXT,
    mensagem_homologacao TEXT,
    mensagem_painel TEXT,
    ano INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

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

if ($action === 'sync' || $action === 'append') {
    // Recebe JSON com array de registros
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if (!$data || !isset($data['records'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }

    // sync: limpa tudo antes de inserir | append: só insere
    if ($action === 'sync') {
        $conn->query("TRUNCATE TABLE credenciamentos");
    }

    $stmt = $conn->prepare("INSERT INTO credenciamentos
        (uf, ibge, ibge_validacao, uf_validacao, municipio, regiao, macrorregiao, regiao_saude,
         ied, tipo, estrategia, nome_completo, portaria, data_portaria, credenciado,
         homologacao_1, homologacao_2, homologacao_3, impacto_1, impacto_2,
         selecao_credenciamento, selecao_homologacao, observacao, mensagem_homologacao, mensagem_painel, ano)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $count = 0;
    foreach ($data['records'] as $r) {
        $stmt->bind_param(
            'siisssssissssissiiiisssssi',
            $r['uf'], $r['ibge'], $r['ibge_validacao'], $r['uf_validacao'], $r['municipio'],
            $r['regiao'], $r['macrorregiao'], $r['regiao_saude'], $r['ied'], $r['tipo'],
            $r['estrategia'], $r['nome_completo'], $r['portaria'], $r['data_portaria'],
            $r['credenciado'], $r['homologacao_1'], $r['homologacao_2'], $r['homologacao_3'],
            $r['impacto_1'], $r['impacto_2'], $r['selecao_credenciamento'], $r['selecao_homologacao'],
            $r['observacao'], $r['mensagem_homologacao'], $r['mensagem_painel'], $r['ano']
        );
        $stmt->execute();
        $count++;
    }

    echo json_encode(['success' => true, 'inserted' => $count]);

} elseif ($action === 'sync_portarias') {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (!$data || !isset($data['records'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }
    $conn->query("TRUNCATE TABLE portarias_aps");
    $stmt = $conn->prepare("INSERT INTO portarias_aps
        (n_portaria, data_portaria, ano, tipo_ato, equipe_servico, link, descricao)
        VALUES (?,?,?,?,?,?,?)");
    // bind: i=n_portaria  s=data_portaria  i=ano  s=tipo_ato  s=equipe_servico  s=link  s=descricao
    $count = 0;
    foreach ($data['records'] as $r) {
        $n  = (int)($r['n_portaria'] ?? 0);
        $dp = $r['data_portaria'] ?: null;
        $an = (int)($r['ano'] ?? 0);
        $ta = (string)($r['tipo_ato'] ?? '');
        $es = (string)($r['equipe_servico'] ?? '');
        $lk = (string)($r['link'] ?? '');
        $de = (string)($r['descricao'] ?? '');
        $stmt->bind_param('isissss', $n, $dp, $an, $ta, $es, $lk, $de);
        $stmt->execute();
        $count++;
    }
    echo json_encode(['success' => true, 'inserted' => $count]);

} elseif ($action === 'sync_represados') {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (!$data || !isset($data['records'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid payload']);
        exit;
    }
    $conn->query("TRUNCATE TABLE represados");
    $stmt = $conn->prepare("INSERT INTO represados
        (ibge, uf, municipio, nro_solicitacao, data_solicitacao, estrategia, status, quantidade, request_type, obs)
        VALUES (?,?,?,?,?,?,?,?,?,?)");
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
        $stmt->bind_param('isssssssss', $ibge, $uf, $mun, $nro, $data_sol, $est, $stat, $qtd, $req_type, $obs);
        $stmt->execute();
        $count++;
    }
    echo json_encode(['success' => true, 'inserted' => $count]);

} elseif ($action === 'portarias') {
    // Retorna todas as portarias da base para o painel
    $result = $conn->query("SELECT * FROM portarias_aps ORDER BY data_portaria DESC");
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);

} elseif ($action === 'represados') {
    // Retorna todos os represados para o painel
    $result = $conn->query("SELECT * FROM represados ORDER BY data_solicitacao DESC");
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);

} elseif ($action === 'data') {
    // Retorna todos os dados para o painel HTML
    $result = $conn->query("SELECT * FROM credenciamentos");
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);

} elseif ($action === 'stats') {
    // Retorna estatísticas resumidas para o painel
    $stats = [];

    $r = $conn->query("SELECT COUNT(*) as total FROM credenciamentos");
    $stats['total'] = $r->fetch_assoc()['total'];

    $r = $conn->query("SELECT COUNT(DISTINCT municipio) as total FROM credenciamentos");
    $stats['municipios'] = $r->fetch_assoc()['total'];

    $r = $conn->query("SELECT COUNT(*) as total FROM credenciamentos WHERE selecao_credenciamento = 'Finalizado'");
    $stats['finalizados'] = $r->fetch_assoc()['total'];

    $r = $conn->query("SELECT uf, COUNT(*) as total FROM credenciamentos GROUP BY uf ORDER BY total DESC");
    $stats['por_uf'] = [];
    while ($row = $r->fetch_assoc()) {
        $stats['por_uf'][] = $row;
    }

    echo json_encode($stats);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action. Use ?action=sync, ?action=data or ?action=stats']);
}

$conn->close();
?>
