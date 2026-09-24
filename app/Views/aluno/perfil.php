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


    <title>Visualizar</title>
    <link rel="icon" type="image/png" href="../../img/icon.png">
</head>

<body>

<?php
$aluno = $aluno ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($aluno[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!--Conteúdo-->
<div class="content">
    <div class="mb-2">
        <!--Botão de voltar-->
        <div class="header-visualizar">

        <a href="/alunos/listar" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
        </a>
            <h2 class="form-title">Visualizar</h2>
        </div>

        <p class="form-subtitle">Confira todas as informações.</p>
    </div>

    <div class="visu-box mb-4">
    <div class="row align-items-center">

        <!-- Foto -->
        <div class="col-lg-3">
            <div class="foto">
                <img src="<?= htmlspecialchars(upload_url($aluno['foto_perfil'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Perfil">
            </div>
        </div>

        <!-- Dados -->
        <div class="col-lg-9">

            <div class="d-flex align-items-center mb-4">
                <h2 class="name me-3"><?= $valor('nome') ?: 'Nome' ?></h2>

                <span class="badge-status">
                    <?= ($aluno['ativo'] ?? false) ? 'Ativo' : 'Inativo' ?>
                </span>
            </div>

            <div class="row">

                <div class="col-md-4">
                    <p class="label-info">
                        <i class="bi bi-person-vcard"></i>
                        RA
                    </p>

                    <span class="value-info">
                        <?= $valor('ra') ?: '—' ?>
                    </span>
                </div>

                <div class="col-md-4">
                    <p class="label-info">
                        <i class="bi bi-calendar-event"></i>
                        Data de nascimento
                    </p>

                    <span class="value-info">
                        <?= $valor('data_nascimento') ?: '—' ?>
                    </span>
                </div>

                <div class="col-md-4">
                    <p class="label-info">
                        <i class="bi bi-gender-ambiguous"></i>
                        Sexo
                    </p>

                    <span class="value-info">
                        <?= $valor('sexo') ?: '—' ?>
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
                <i class="bi bi-telephone-fill"></i>
                Contato
                </h5>
                <div class="info-grid">

                    <div class="info-item">
                        <h6>E-mail</h6>
                        <p><?= htmlspecialchars($aluno['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                        <br>
                        <h6>Telefone</h6>
                        <p><?= $valor('telefone') ?: '—' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
        <div class="visu-box mb-4">
            <h5>
            <i class="bi bi-geo-alt-fill"></i>
            Endereço
            </h5>
            <div class="info-grid">
                <div class="info-item">
                    <h6>CEP</h6>
                    <p><?= $valor('cep') ?: '—' ?></p>
                    <br>
                    <h6>Escola</h6>
                    <p><?= htmlspecialchars($aluno['escola'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>  
        </div> 

    </div>

</div>

    <script>
        window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
        email: <?= json_encode($usuario['email'] ?? '—') ?>, 
        foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };
    </script>

    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>
</body>