<?php
$dados = $dados ?? [];

$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');

$usuarios = $usuarios?? [];
$times = $times ?? [];
$responsaveis = $responsaveis ?? [];

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
    <app-header></app-header>

    <!-- Conteúdo -->
<div class="content">
    <div class="card form-card shadow-sm">

        <form action="/times/adicionar-responsavel?id=<?= htmlspecialchars($times['cd_time'] ?? 0, ENT_QUOTES, 'UTF-8') ?>" method="POST">
            <input type="hidden" name="id_time" value="<?= htmlspecialchars($times['cd_time'] ?? 0, ENT_QUOTES, 'UTF-8') ?>">
            <div id="etapa1">
                <h4 class="form-title">Adicionar Responsável do Time</h4>
                <p class="form-subtitle">
                    Preencha os dados do Responsável.
                </p>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="times" class="form-label">Time</label>
                        <select id="times" name="times" class="form-select form-input" disabled required>
                            <option value="<?= htmlspecialchars($times['cd_time']) ?>" >
                                <?= htmlspecialchars($times['nm_time']) ?>
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="cd_responsavel">Responsável</label>
                        <select id="cd_responsavel" name="cd_responsavel" class="form-select form-input" required>
                            <option value="">Selecione</option>
                            <?php foreach ($responsaveis as $responsavel): ?>
                                <option value="<?= (int) $responsavel['cd_responsavel'] ?>">
                                    <?= htmlspecialchars($responsavel['nm_usuario'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="actions full d-flex justify-content-end gap-3 mt-4">
                    <a href="/times/visualizar?id=<?= htmlspecialchars($times['cd_time'] ?? 0, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Cancelar</a>
                    <button class="btn btn-laranja" type="submit">Vincular responsável</button>
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
</html>