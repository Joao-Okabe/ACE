<?php
$time = $time ?? [];
$valor = static fn (string $campo): string => htmlspecialchars((string) ($time[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/geral.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/visualizar.css">
    <link rel="stylesheet" href="../../css/acessibilidade.css">
    <title>Visualizar time</title>
</head>
<body>
<app-header></app-header>
<div class="content">
    <div class="header-visualizar mb-4">
        <a href="/times/listar" class="btn-voltar"><i class="bi bi-arrow-left"></i></a>
        <h2 class="form-title">Visualizar time</h2>
    </div>

    <div class="visu-box">
        <div class="row align-items-center">
            <div class="col-lg-3">
                <div class="foto">
                    <img src="<?= htmlspecialchars(upload_url($time['path_brasao'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Brasao do time">
                </div>
            </div>
            <div class="col-lg-9">
                <h2 class="name mb-4"><?= $valor('nm_time') ?></h2>
                <p>Codigo</p>
                <p><?= $valor('cd_time') ?></p>
                <p>Status</p>
                <p><?= ($time['ativo'] ?? false) ? 'Ativo' : 'Inativo' ?></p>
            </div>
        </div>
    </div>
</div>
<script src="../../js/script.js"></script>
<script src="../../js/layout.js"></script>
<script src="../../js/acessibilidade.js"></script>
</body>
</html>
