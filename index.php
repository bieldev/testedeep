<?php
session_start();
// Verifica se o usuário já tem um ID, senão cria um novo
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = uniqid('user_', true);
}

// Carrega os créditos do usuário
$creditos_file = 'data/usuarios.json';
$usuarios = file_exists($creditos_file) ? json_decode(file_get_contents($creditos_file), true) : [];
$user_id = $_SESSION['user_id'];
$creditos = $usuarios[$user_id]['creditos'] ?? 5; // 5 créditos iniciais
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Proprietários</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center">Consulta de Proprietários</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5>Seus Créditos:</h5>
                            <span id="creditos-display" class="badge bg-success fs-5"><?= $creditos ?></span>
                        </div>
                        <div>
        <span id="creditos-display" class="badge bg-success fs-5 me-2"><?= $creditos ?></span>
        <a href="minhas_consultas.php" class="btn btn-sm btn-outline-primary">Minhas Consultas</a>
    </div>
                        <form id="consulta-form">
                            <div class="mb-3">
                                <label for="endereco" class="form-label">Endereço Completo:</label>
                                <textarea class="form-control" id="endereco" rows="4" required placeholder="Ex: Av. Paulista, 1000 - Bela Vista - São Paulo/SP"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="consultar-btn">
                                Procurar Proprietário
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Consulta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Esta consulta irá consumir 1 crédito. Deseja continuar?</p>
                    <p><strong>Endereço:</strong> <span id="endereco-preview"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirm-consulta">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    // Define a variável global antes de carregar o script
    var USER_ID = '<?= $_SESSION['user_id'] ?>';
</script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>