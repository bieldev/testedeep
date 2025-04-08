<?php
header('Content-Type: application/json');
session_start();

// Verifica autenticação
if (!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit;
}

// Validação básica
if (empty($_POST['id']) || empty($_POST['acao'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

$consulta_id = $_POST['id'];
$acao = $_POST['acao'];

// Caminhos dos arquivos
$consultas_file = 'data/consultas.json';
$usuarios_file = 'data/usuarios.json';

// Carrega os dados
$consultas = file_exists($consultas_file) ? json_decode(file_get_contents($consultas_file), true) : [];
$usuarios = file_exists($usuarios_file) ? json_decode(file_get_contents($usuarios_file), true) : [];

// Encontra a consulta
$consulta_index = null;
foreach ($consultas as $index => $consulta) {
    if ($consulta['id'] === $consulta_id) {
        $consulta_index = $index;
        break;
    }
}

if ($consulta_index === null) {
    echo json_encode(['success' => false, 'message' => 'Consulta não encontrada.']);
    exit;
}

// Processa a ação
if ($acao === 'responder') {
    // Valida a resposta
    if (empty($_POST['resposta'])) {
        echo json_encode(['success' => false, 'message' => 'Resposta não pode estar vazia.']);
        exit;
    }
    
    // Atualiza a consulta
    $consultas[$consulta_index]['status'] = 'respondido';
    $consultas[$consulta_index]['resposta'] = htmlspecialchars($_POST['resposta'], ENT_QUOTES, 'UTF-8');
    
    // Não devolve crédito - já foi debitado quando criou a consulta
} elseif ($acao === 'cancelar') {
    // Verifica se já não está cancelada
    if ($consultas[$consulta_index]['status'] === 'cancelado') {
        echo json_encode(['success' => false, 'message' => 'Consulta já está cancelada.']);
        exit;
    }
    
    // Atualiza status
    $consultas[$consulta_index]['status'] = 'cancelado';
    
    // Devolve o crédito ao usuário
    $user_id = $consultas[$consulta_index]['usuario'];
    if (isset($usuarios[$user_id])) {
        $usuarios[$user_id]['creditos']++;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
    exit;
}

// Salva os dados
try {
    file_put_contents($consultas_file, json_encode($consultas, JSON_PRETTY_PRINT));
    file_put_contents($usuarios_file, json_encode($usuarios, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar os dados: ' . $e->getMessage()
    ]);
}
?>