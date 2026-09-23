<?php

$times = $times ?? [];
$escolas = $escola ?? []; 

$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');

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

    <title>Times</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!--Conteúdo-->  
<div class="content">  
    <div class="d-flex flex-column flex-md-row justify-content-between  mb-4">
        <div>
            <h2 class="form-title">Times</h2>
            <p class="form-subtitle">Gerencie os times da sua escola.</p>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between  mb-4">
            <a href="/times/criar" class="btn btn-laranja">
            + Adicionar time
            </a>
        </div>
    </div>

    <!-- Card -->
    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-4">
                <form class="row gx-2 gy-2 align-items-center w-100" method="get" action="/times/listar">
                    <div class="col-md-4">
                        <div class="input-group pesquisa-escola">
                            <div class="form-input-group search-box">
                                <i class="bi bi-search"></i>
                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control form-input"
                                    placeholder="Pesquisar times"
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
                <th scope="col">Brasão</th>
                <th scope="col">Código</th>
                <th scope="col">Nome</th>
                <th scope="col">Oficial</th>
                <th scope="col">Ações</th>
              </tr>
            </thead>

            <tbody class="table-group-divider">
            <?php foreach ($times as $time): ?>
                <tr>
                    <td>
                        <div class="tc list-perfil">
                            <img src="<?= htmlspecialchars(upload_url($time['path_escudo'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Perfil">
                        </div>
                    </td>
                    <td><?= htmlspecialchars($time['cd_time']) ?></td>
                    <td><?= htmlspecialchars($time['nm_time']) ?></td>
                    <td><?= htmlspecialchars($time['principal'] ?? '') ?></td>
                    <td class="acoes">
                        <a href="/times/visualizar?id=<?= urlencode($time['cd_time']) ?>" class="btn btn-view btn-sm me-1" title="Visualizar">
                            <i class="bi bi-eye-fill"></i>
                        </a>

                        <a href="/times/editar?id=<?= urlencode($time['cd_time']) ?>" class="btn btn-edit btn-sm me-1" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </a>

                        <form method="post" action="/times/remover" class="form-excluir">
                            <input type="hidden" name="id" value="<?= (int) $time['cd_time'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-delete btn-sm" title="Excluir">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
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
                <h3 id="modalExcluirTitulo">Excluir aluno?</h3>
                <p>
                    Tem certeza de que deseja excluir este aluno? <br>
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
    <script src="../../js/acessibilidade.js"></script>

</body>
</html>