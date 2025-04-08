<?php
header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verifica dados recebidos
if (empty($_POST['endereco']) || empty($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    // Configurações
    $data_dir = __DIR__ . '/data/';
    
    // Garante que o diretório data existe
    if (!file_exists($data_dir)) {
        if (!mkdir($data_dir, 0755, true)) {
            throw new Exception('Não foi possível criar o diretório de dados');
        }
    }
    
    // Processa a consulta
    $endereco = htmlspecialchars($_POST['endereco'], ENT_QUOTES, 'UTF-8');
    $user_id = $_POST['user_id'];
    
    // Carrega ou cria os arquivos JSON
    $consultas_file = $data_dir . 'consultas.json';
    $usuarios_file = $data_dir . 'usuarios.json';
    
    $consultas = file_exists($consultas_file) ? json_decode(file_get_contents($consultas_file), true) : [];
    $usuarios = file_exists($usuarios_file) ? json_decode(file_get_contents($usuarios_file), true) : [];
    
    // Verifica créditos
    if (!isset($usuarios[$user_id])) {
        $usuarios[$user_id] = ['creditos' => 5];
    }
    
    if ($usuarios[$user_id]['creditos'] < 1) {
        echo json_encode(['success' => false, 'message' => 'Créditos insuficientes']);
        exit;
    }
    
    // Cria nova consulta
    $nova_consulta = [
        'id' => uniqid('cons_', true),
        'usuario' => $user_id,
        'endereco' => $endereco,
        'data' => date('Y-m-d H:i:s'),
        'status' => 'pendente',
        'resposta' => ''
    ];
    
    // Atualiza dados
    $consultas[] = $nova_consulta;
    $usuarios[$user_id]['creditos']--;
    
    // Salva os arquivos
    if (file_put_contents($consultas_file, json_encode($consultas, JSON_PRETTY_PRINT)) === false) {
        throw new Exception('Erro ao salvar consultas');
    }
    
    if (file_put_contents($usuarios_file, json_encode($usuarios, JSON_PRETTY_PRINT)) === false) {
        throw new Exception('Erro ao salvar usuários');
    }
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'novos_creditos' => $usuarios[$user_id]['creditos']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro no servidor: ' . $e->getMessage()
    ]);
}