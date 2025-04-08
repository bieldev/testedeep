<?php
header('Content-Type: application/json');
session_start();

// Verifica autenticação para admin
if (strpos($_SERVER['REQUEST_URI'], 'admin') !== false && 
    (!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true)) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
    exit;
}

// Validação básica
if (empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não informado.']);
    exit;
}

$consulta_id = $_GET['id'];

// Carrega as consultas
$consultas_file = 'data/consultas.json';
$consultas = file_exists($consultas_file) ? json_decode(file_get_contents($consultas_file), true) : [];

// Encontra a consulta
$consulta_encontrada = null;
foreach ($consultas as $consulta) {
    if ($consulta['id'] === $consulta_id) {
        $consulta_encontrada = $consulta;
        break;
    }
}

if ($consulta_encontrada === null) {
    echo json_encode(['success' => false, 'message' => 'Consulta não encontrada.']);
    exit;
}

echo json_encode($consulta_encontrada);
?>