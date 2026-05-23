<?php
// Chave de segurança - o Python deve enviar essa chave em cada requisição
define('API_KEY', 'painel_cred_2026_key');

// Configurações do banco
define('DB_HOST', 'localhost');
define('DB_USER', 'u127731061_0swzh');
define('DB_PASS', 'fEfK.+blBs7K6U()');
define('DB_NAME', 'u127731061_Zpe5T');

header('Content-Type: application/json');

// Verifica a chave de segurança
$headers = getallheaders();
if (!isset($headers['X-Api-Key']) || $headers['X-Api-Key'] !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset('utf8mb4');

// Cria a tabela se não existir
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
