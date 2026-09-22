<?php

$possuiADM = $possuiADM ?? [];
$escolas = $escolas ?? [];

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
    <link rel="stylesheet" href="../../css/acessibilidade.css">

    <title>Escolas</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!--Conteúdo-->  
<div class="content">  
    <div class="d-flex flex-column flex-md-row justify-content-between  mb-4">
        <div>
            <h2 class="form-title">Escolas</h2>
            <p class="form-subtitle">Gerencie as escolas cadastradas.</p>
        </div>
        <?php if ($possuiADM): ?>
        <div class="d-flex flex-column flex-md-row justify-content-between  mb-4">
            <a href="/escolas/cadastrar" class="btn btn-laranja">
            + Adicionar escola
            </a>
        </div>
        <?php endif ?>
    </div>

    <!-- Card da tabela-->
    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-4">
                <form class="row g-3 align-items-end w-100" method="get" action="/escolas/listar">

                    <div class="col-md-4">
                        <div class="input-group pesquisa-escola">
                            <div class="form-input-group search-box">
                                <i class="bi bi-search"></i>
                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control form-input"
                                    placeholder="Pesquisar Escola"
                                    value="<?= htmlspecialchars($filtros['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-input-group btn-filtro">
                            <select name="categoria" class="form-select form-input">
                                <option value="">Todas as categorias</option>
                                <?php foreach (['Escola Municipal', 'Escola Estadual', 'Privada'] as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= ($filtros['categoria'] ?? '') === $categoria ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
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

            <!-- Tabela -->
            <div class="list mt-4">
                <div class="card card-list">

                    <div class="table-responsive">
                    <table class="table table-striped table-borderless mb-0">

                    <thead class="table-blue">
                    <tr>
                        <th scope="col">Brasão</th>
                        <th scope="col">Código</th>
                        <th scope="col">Nome</th>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          
                        <th scope="col">Telefone</th>
                        <th scope="col">CEP</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Ações</th>
                    </tr>
                    </thead>
                <tbody class="table-group-divider">
                <?php foreach ($escolas as $escola): ?>
                    <tr>
                        <td>
                            <div class="tc list-perfil">
                                <img src="<?= htmlspecialchars(upload_url($escola['img_logo'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Brasão">
                            </div>
                        </td>
                        <td><?= htmlspecialchars($escola['cd_escola']) ?></td>
                        <td><?= htmlspecialchars($escola['nome']) ?></td>
                        <td><?= htmlspecialchars($escola['telefone']) ?></td>
                        <td><?= htmlspecialchars($escola['cep']) ?></td>
                        <td><?php if (($escola['ativa']) == 1 ) { echo "Ativa"; } else { echo "Inativa"; } ?></td>
                        <td><?= htmlspecialchars($escola['categoria_administrativa']) ?></td>
                        <td class="acoes">
                        <!--Btn Visualizar -->
                        <a href="/escolas/visualizar?id=<?= urlencode($escola['cd_escola']) ?>" class="btn btn-view btn-sm" title="Visualizar">
                            <i class="bi bi-eye-fill"></i>
                        </a>

                    <?php 
                        $usuarioAtual = $_SESSION['usuario'] ?? [];
                        $ehAdministrador = in_array('ADM', $usuarioAtual['papeis'] ?? [], true);

                        $mostrarBotaoEditarExcluir = $ehAdministrador;
                        if (!$mostrarBotaoEditarExcluir && !empty($usuarioAtual['id'])) {
                            $vinculoModel = new VinculoUsuarioEscola();
                            $mostrarBotaoEditarExcluir = $vinculoModel->isUsuarioDiretor((int) $usuarioAtual['id'], (int) ($escola['cd_escola'] ?? 0));
                        }
                        if ($mostrarBotaoEditarExcluir): ?>
                        <!--Btn Editar -->
                        <a href="/escolas/editar?id=<?= urlencode($escola['cd_escola']) ?>" class="btn btn-edit btn-sm" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <!--Btn Excluir -->
                        <a href="/escolas/remover?id=<?= urlencode($escola['cd_escola']) ?>" class="btn btn-delete btn-sm" title="Excluir">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    <?php endif ?>
                        </td>
                <?php endforeach; ?>
                    </tr>
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
                <h3 id="modalExcluirTitulo">Excluir escola?</h3>
                <p>
                    Tem certeza de que deseja excluir está escola? <br>
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