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

    <title>Escolas</title>
</head>
<body>
<!--Navbar-->
    <nav class="navbar">
            <button id="menu-btn">
                <i class="bi bi-layout-sidebar"></i>
            </button>

            <div class="logo">
                <img src="../../img/logo-ace-laranja.png" alt="logo ace">
            </div>

            <div class="perfil">
                <i class="bi bi-bell notificacao"></i>
                <img src="<?= htmlspecialchars(upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Perfil">

            <div class="usuario">
                <?php if ($usuario !== null): ?>
                <span class="session-info">
                    <?= htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>

                <span class="session-info">
                    Perfil: <?= htmlspecialchars(implode(', ', $usuario['papeis'] ?? []), ENT_QUOTES, 'UTF-8') ?>
                </span>
                <?php else: ?>
                    <p class="alert error">Sessão inválida.</p>
                <?php endif; ?>
            </div>
            </div>
    </nav>

<!--Sidebar-->
    <div class="sidebar">
        <div class="menu">

            <a href="/dashboard" class="menu-item">
                <i class="bi bi-house-door-fill"></i>
                <span>Painel</span>
            </a>

            <a href="/escolas/listar" class="menu-item active">
                <i class="bi bi-bank2"></i>
                <span>Escolas</span>
            </a>

            <a href="/alunos/listar" class="menu-item">
                <i class="bi bi-person-fill"></i>
                <span>Alunos</span>
            </a>

            <a href="..." class="menu-item ">
                <i class="bi bi-people-fill"></i>
                <span>Times</span>
            </a>

            <a href="..." class="menu-item ">
                <i class="bi bi-trophy-fill"></i>
                <span>Competições</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-dribbble"></i>
                <span>Partidas</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Rankings</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-gear-fill"></i>
                <span>Configurações</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair da conta</span>
            </a>

        </div>
    </div>

<!-- Div de inicio -->    
    <div class="content">  
        <div class="d-flex justify-content-between align-items-center mb-4">
        
        <div>
            <h2 class="form-title">Escolas</h2>
            <p>Gerencie as escolas cadastradas.</p>
        </div>
            <a href="cadastro.php" class="btn btn-laranja">
            + Adicionar escola
            </a>
        </div>

<!-- Card da tabela-->
        <div class="card shadow">
            <div class="card-body">
            <div class="d-flex justify-content-between mb-4">

                <div class="input-group w-25">
                    <div class="form-input-group">
                        <input type="text" class="form-control form-input" placeholder="Pesquisar escola">
                    </div>
                </div>                      

                <button class="btn btn-outline-secondary">
                    <i class="bi bi-funnel"></i>
                    Filtros
                </button>

            </div>

<!-- Tabela -->
            <div class="list mt-4">      
                    <div class="card card-list">
                    <table class="table table-striped table-borderless mb-0">
                        
                    <thead class="table-blue">
                    <tr>
                        <th scope="col">Brasão</th>
                        <th scope="col">Código da Escola</th>
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
                        <td>
                        <!--Btn Visualizar -->
                        <button class="btn btn-view btn-sm" title="Visualizar">
                            <a href="">
                            <i class="bi bi-eye-fill"></i>
                            </a>
                        </button>
                        <!--Btn Editar -->
                        <button class="btn btn-edit btn-sm" title="Editar">
                            <a href="/escolas/editar?id=<?= urlencode($escola['cd_escola']) ?>"> 
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        </button>
                        <!--Btn Excluir -->
                        <button class="btn btn-delete btn-sm" title="Excluir">
                            <a href="">
                            <i class="bi bi-trash-fill"></i>
                            </a>
                        </button>
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
    <!--Js para o funcionamento da sidebar-->>
    <script src="../../js/script.js"></script>
</body>
</html>