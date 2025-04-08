<?php
session_start();

// Verifica se o usuário está "logado" (tem ID na sessão)
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Carrega as consultas do arquivo JSON
$consultas_file = 'data/consultas.json';
$consultas = file_exists($consultas_file) ? json_decode(file_get_contents($consultas_file), true) : [];

// Filtra apenas as consultas do usuário atual
$minhas_consultas = array_filter($consultas, function($consulta) use ($user_id) {
    return $consulta['usuario'] === $user_id;
});

// Ordena do mais recente para o mais antigo
usort($minhas_consultas, function($a, $b) {
    return strtotime($b['data']) - strtotime($a['data']);
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Consultas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Minhas Consultas</h3>
                            <a href="index.php" class="btn btn-light btn-sm">Nova Consulta</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($minhas_consultas)): ?>
                            <div class="alert alert-info">Você ainda não realizou nenhuma consulta.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Endereço</th>
                                            <th>Status</th>
                                            <th>Resposta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($minhas_consultas as $consulta): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($consulta['data'])) ?></td>
                                                <td><?= htmlspecialchars($consulta['endereco']) ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?= $consulta['status'] === 'pendente' ? 'bg-warning' : 
                                                           ($consulta['status'] === 'respondido' ? 'bg-success' : 'bg-danger') ?>">
                                                        <?= ucfirst($consulta['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($consulta['status'] === 'respondido' && !empty($consulta['resposta'])): ?>
                                                        <button class="btn btn-sm btn-outline-primary ver-resposta-btn" 
                                                                data-resposta="<?= htmlspecialchars($consulta['resposta']) ?>">
                                                            Ver Resposta
                                                        </button>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar a resposta -->
    <div class="modal fade" id="respostaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resposta da Consulta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resposta-modal-body">
                    <!-- A resposta será inserida aqui via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Mostra a resposta no modal
            $('.ver-resposta-btn').on('click', function() {
                const resposta = $(this).data('resposta');
                $('#resposta-modal-body').html(resposta.replace(/\n/g, '<br>'));
                new bootstrap.Modal(document.getElementById('respostaModal')).show();
            });
        });
    </script>
</body>
</html>