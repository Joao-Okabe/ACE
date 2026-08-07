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
    <link rel="stylesheet" href="../../css/visualizar.css">

    <title>Painel</title>
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
        <img src="img/perfil.jpg" alt="Perfil">

        <div class="usuario">
            <span>Gestão</span>
            <small>email@com</small>
        </div>
    </div>
</nav>

<!--Sidebar-->
<div class="sidebar">
    <div class="menu">

        <a href="./../auth/painel.php" class="menu-item">
            <i class="bi bi-house-door-fill"></i>
            <span>Painel</span>
        </a>

        <a href="../../Views/escola/lista.php" class="menu-item active">
            <i class="bi bi-bank2"></i>
            <span>Escolas</span>
        </a>

        <a href="../../Views/aluno/lista.php" class="menu-item">
            <i class="bi bi-person-fill"></i>
            <span>Alunos</span>
        </a>

        <a href="..." class="menu-item">
            <i class="bi bi-people-fill"></i>
            <span>Times</span>
        </a>

        <a href="..." class="menu-item">
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

<?php
$escola = $escola ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($escola[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div class="content">
    <div class="mb-2">
        <!--Botão de voltar-->
        <div class="header-visualizar">

        <a href="/escolas/listar" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
        </a>
            <h2 class="form-title">Visualizar</h2>
        </div>

        <p class="form-subtitle">Confira todas as informações.</p>
    </div>

    <div class="visu-box mb-4">
    <div class="row g-4 align-items-center">

        <!-- Foto -->
        <div class="col-lg-3">
            <div class="foto">
                <img src="<?= htmlspecialchars(upload_url($escola['img_logo'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Perfil">
            </div>
        </div>

        <!-- Dados -->
        <div class="col-lg-9">

            <div class="d-flex align-items-center mb-4">
                <h2 class="name me-3"><?= $valor('nome') ?></h2>

                <span class="badge-status">
                    <?= ($escola['ativa'] ?? false) ? 'Ativa' : 'Inativa' ?>
                </span>
            </div>

            <div class="row">

                <div class="col-md-4">
                    <p class="label-info">
                        <i class="bi bi-person-vcard"></i>
                        Categoria
                    </p>

                    <span class="value-info">
                        <?= $valor('categoria_administrativa') ?: '—' ?>
                    </span>
                </div>

            </div>

        </div>

    </div>
</div>

    <div class="row">

        <div class="col-lg-6">
            <div class="visu-box mb-4">
                <h5>
                <i class="bi bi-info-circle-fill"></i>
                Informações Gerais
                </h5>
                <div class="info-grid">
                    <div class="info-item">
                        <h6>Nome da escola</h6>
                        <p><?= $valor('nome') ?: '—' ?></p>
                        <br>
                        <h6>Categoria</h6>
                        <p><?= $valor('categoria_administrativa') ?: '—' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="visu-box mb-4">
                <h5>
                <i class="bi bi-telephone-fill"></i>
                Contato
                </h5>
                <div class="info-grid">

                    <div class="info-item">
                        <h6>E-mail</h6>
                        <p>—</p>
                        <br>
                        <h6>Telefone</h6>
                        <p><?= $valor('telefone') ?: '—' ?></p>
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
        <div class="visu-box mb-4">
            <h5>
            <i class="bi bi-geo-alt-fill"></i>
            Endereço
            </h5>
            <div class="info-grid">
                    <div class="info-item">
                        <h6>CEP</h6>
                        <p><?= $valor('cep') ?: '—' ?></p>
                    </div>

                    <div class="info-item">
                        <h6>Número</h6>
                        <p><?= $valor('numero') ?: '—' ?></p>
                    </div>
            </div>
        </div>   
    </div>
    </div>

</div>



    <script src="../../js/script.js"></script>
</body>