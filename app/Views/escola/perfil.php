<?php
$escola = $escola ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($escola[$campo] ?? '', ENT_QUOTES, 'UTF-8');
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
    <link rel="stylesheet" href="../../css/visualizar.css">
    <link rel="stylesheet" href="../../css/acessibilidade.css">

    <title>Painel</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>
    
<!-- Navbar e Sidebar -->
<app-header></app-header>

<!--Conteúdo-->
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

            <?php if (!empty($flash)): ?>
                <?php if (!empty($flash['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if (!empty($flash['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($flash['error'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            // Mostrar botão de trancar/destrancar apenas se o usuário for Diretor vinculado
            $mostrarBotaoTrancar = false;
            if (!empty($usuario['id'])) {
                $vinculoModel = new VinculoUsuarioEscola();
                $mostrarBotaoTrancar = $vinculoModel->isUsuarioDiretor(
                    (int) $usuario['id'], 
                    (int) ($escola['cd_escola'] ?? 0)
                );
            }
            ?>

            <?php if ($mostrarBotaoTrancar): ?>
                <div class="mb-3">
                    <form method="post" action="/escolas/trancar" onsubmit="return confirm('Tem certeza?');">
                        <input type="hidden" name="id" value="<?= (int) ($escola['cd_escola'] ?? 0) ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (($escola['ativa'] ?? false)): ?>
                            <button type="submit" class="btn btn-danger">Trancar</button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-success">Destrancar</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>

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

    <script>
        window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
        email: <?= json_encode($usuario['email'] ?? '—') ?>, 
        foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };
    </script>

    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>
    <script src="../../js/acessibilidade.js"></script>

</body>