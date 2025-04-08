<?php
session_start();

// Verifica autenticação
if (!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true) {
    header('Location: admin_auth.php');
    exit;
}

// Carrega as consultas
$consultas_file = 'data/consultas.json';
$consultas = file_exists($consultas_file) ? json_decode(file_get_contents($consultas_file), true) : [];

// Carrega usuários
$usuarios_file = 'data/usuarios.json';
$usuarios = file_exists($usuarios_file) ? json_decode(file_get_contents($usuarios_file), true) : [];

// Processa filtros
$filtro_status = $_GET['status'] ?? 'todos';
$consultas_filtradas = array_filter($consultas, function($consulta) use ($filtro_status) {
    return $filtro_status === 'todos' || $consulta['status'] === $filtro_status;
});

// Ordena por data (mais recente primeiro)
usort($consultas_filtradas, function($a, $b) {
    return strtotime($b['data']) - strtotime($a['data']);
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Administrativa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Admin - Consultas</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-outline-light">Sair</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <h2>Consultas de Proprietários</h2>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <a href="?status=todos" class="btn btn-outline-primary <?= $filtro_status === 'todos' ? 'active' : '' ?>">Todos</a>
                    <a href="?status=pendente" class="btn btn-outline-warning <?= $filtro_status === 'pendente' ? 'active' : '' ?>">Pendentes</a>
                    <a href="?status=respondido" class="btn btn-outline-success <?= $filtro_status === 'respondido' ? 'active' : '' ?>">Respondidos</a>
                    <a href="?status=cancelado" class="btn btn-outline-danger <?= $filtro_status === 'cancelado' ? 'active' : '' ?>">Cancelados</a>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="consultas-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Endereço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultas_filtradas as $consulta): ?>
                        <tr>
                            <td><?= substr($consulta['id'], 0, 8) ?>...</td>
                            <td><?= date('d/m/Y H:i', strtotime($consulta['data'])) ?></td>
                            <td><?= substr($consulta['usuario'], 0, 8) ?>...</td>
                            <td><?= htmlspecialchars($consulta['endereco']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $consulta['status'] ?>">
                                    <?= ucfirst($consulta['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($consulta['status'] === 'pendente'): ?>
                                    <button class="btn btn-sm btn-success responder-btn" data-id="<?= $consulta['id'] ?>">Responder</button>
                                    <button class="btn btn-sm btn-danger cancelar-btn" data-id="<?= $consulta['id'] ?>">Cancelar</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-info ver-resposta-btn" data-id="<?= $consulta['id'] ?>">Ver Resposta</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal de Resposta -->
    <div class="modal fade" id="respostaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Responder Consulta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Endereço:</label>
                        <p id="modal-endereco" class="form-control-static"></p>
                    </div>
                    <div class="mb-3">
                        <label for="resposta-texto" class="form-label">Resposta:</label>
                        <textarea class="form-control" id="resposta-texto" rows="6"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="enviar-resposta-btn">Enviar Resposta</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ver Resposta -->
    <div class="modal fade" id="verRespostaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resposta da Consulta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Endereço:</label>
                        <p id="ver-modal-endereco" class="form-control-static"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resposta:</label>
                        <div id="ver-resposta-texto" class="p-3 bg-light rounded"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>