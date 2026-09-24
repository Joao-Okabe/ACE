<?php
$usuario = $usuario ?? null;

$escolas = $escolas ?? [];
$competicoes = $competicoes ?? [];
$podeGerenciar = $podeGerenciar ?? [];

// Gera CSRF token se necessário
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Flash messages
$flash = $_SESSION['flash'] ?? null;
if (!empty($_SESSION['flash'])) {
    unset($_SESSION['flash']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Bootstrap css-->
    <link rel="stylesheet" href="../../bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <!--Bootstrap icons-->
    <link rel="stylesheet" href="../../bootstrap-icons-1.13.1/bootstrap-icons.css">

    <!--CSS-->
    <link rel="stylesheet" href="../../css/geral.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/lista.css">


    <title>Competições</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>
    
<!-- Navbar e Sidebar -->
<app-header></app-header>

<!--Conteúdo-->
<div class="content">  
    <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
        <div>
            <h2 class="form-title">Competições</h2>
            <p class="form-subtitle">Gerencie as competições.</p>
        </div>
            <a href="/competicoes/criar" class="btn btn-laranja">
            + Adicionar competição
            </a>
    </div>

    <!-- Card -->
    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-4">
                <form class="row gx-2 gy-2 align-items-center w-100" method="GET" action="/competicoes/listar">
                    <div class="col-md-4">
                        <div class="input-group pesquisa-escola">
                            <div class="form-input-group search-box">
                                <i class="bi bi-search"></i>
                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control form-input"
                                    placeholder="Pesquisar competições"
                                    value="<?= htmlspecialchars($filtros['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-input-group btn-filtro">
                            <select name="escola" class="form-select form-input">
                                <option value="">Todas as escolas</option>
                                <?php foreach ($escolas as $escola): ?>
                                    <option value="<?= htmlspecialchars($escola['cd_escola'], ENT_QUOTES, 'UTF-8') ?>" <?= isset($filtros['escola']) && $filtros['escola'] == $escola['cd_escola'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($escola['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-input-group btn-filtro">
                            <select name="ordem" class="form-select form-input">
                                <option value="asc" <?= ($filtros['ordem'] ?? 'asc') === 'asc' ? 'selected' : '' ?>>Código crescente</option>
                                <option value="desc" <?= ($filtros['ordem'] ?? 'asc') === 'desc' ? 'selected' : '' ?>>Código decrescente</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-buscar">Buscar</button>
                    </div>
                </form>
            </div>

    <!-- tabela -->
    <?php if (!empty($flash)): ?>
        <?php if (!empty($flash['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($flash['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="list mt-4">      
        <div class="card card-list">
            <div class="table-responsive">
            <table class="table table-striped table-borderless mb-0">
                
            <thead class="table-blue">
              <tr>
                <th scope="col">Código</th>
                <th scope="col">Nome</th>
                <th scope="col">Criador</th>
                <th scope="col">Data de Ínicio</th>
                <th scope="col">Data de Encerramento</th>
                <th scope="col">Ações</th>
              </tr>
            </thead>

            <tbody class="table-group-divider">

            <?php foreach ($competicoes as $competicao): ?>
                <tr>
                    <td><?= htmlspecialchars($competicao['cd_competicao']) ?></td>
                    <td><?= htmlspecialchars($competicao['nm_competicao']) ?></td>
                    <td><?= htmlspecialchars($competicao['cd_criador'] ?? '') ?></td>
                    <td><?= htmlspecialchars(substr((string) ($competicao['inicio_em'] ?? ''), 0, 10)) ?></td>
                    <td><?= htmlspecialchars(substr((string) ($competicao['fim_em'] ?? ''), 0, 10)) ?></td>
                    <?php
                        $idCompeticao = (int) ($competicao['cd_competicao'] ?? 0);
                        $podeEditarExcluir = $podeGerenciar[$idCompeticao] ?? false;
                    ?>
                    <td class="acoes">
                        <a href="/competicoes/visualizar?id=<?= urlencode($competicao['cd_competicao']) ?>" class="btn btn-view btn-sm me-1" title="Visualizar">
                            <i class="bi bi-eye-fill"></i>
                        </a>

                        <?php if ($podeEditarExcluir): ?>
                            <a href="/competicoes/editar?id=<?= urlencode($competicao['cd_competicao']) ?>" class="btn btn-edit btn-sm me-1" title="Editar">
                                <i class="bi bi-pencil-fill"></i>
                            </a>

                            <form method="post" action="/competicoes/remover" class="form-excluir">
                                <input type="hidden" name="id" value="<?= (int) $competicao['cd_competicao'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn-delete btn-sm" title="Excluir">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
            </div>
        </div>
    </div>

        </div>
    </div>   
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-excluir">

            <div class="modal-header">

            </div>

            <div class="modal-body text-center">
                <h3 id="modalExcluirTitulo">Excluir competição?</h3>
                <p>
                    Tem certeza de que deseja excluir está competição? <br>
                    Essa ação não poderá ser desfeita.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-confirmar-exclusao" id="confirmarExclusao">
                    <i class="bi bi-trash-fill"></i>
                    Excluir
                </button>
            </div>

        </div>
    </div>
</div>

<script src="../../bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
<script>
    let formularioExcluir = null;

    const modalExcluirElement = document.getElementById('modalExcluir');
    const modalExcluir = new bootstrap.Modal(modalExcluirElement);
    const confirmarExclusao = document.getElementById('confirmarExclusao');

    document.querySelectorAll('.form-excluir').forEach(formulario => {
        formulario.addEventListener('submit', function (event) {
            event.preventDefault();

            formularioExcluir = this;
            modalExcluir.show();
        });
    });

    confirmarExclusao.addEventListener('click', function () {
        if (formularioExcluir) {
            formularioExcluir.submit();
        }
    });
</script>

    <script>
        window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
        email: <?= json_encode($usuario['email'] ?? '—') ?>, 
        foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };
    </script>

    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>
</body>
</html>