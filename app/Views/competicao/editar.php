<?php
$usuario = $usuario ?? null;
$competicao = $competicao ?? [];
$valor = static fn (string $campo): string => htmlspecialchars((string) ($competicao[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
$data = static fn (string $campo): string => htmlspecialchars(substr((string) ($competicao[$campo] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8');
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
    <link rel="stylesheet" href="../../css/acessibilidade.css">
    <title>Editar competição</title>
</head>
<body>
<app-header></app-header>
<div class="content">
    <div class="card form-card shadow-sm">
        <h2 class="form-title">Editar competição</h2>
        <?php if (!empty($erro)): ?>
            <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form action="/competicoes/atualizar?id=<?= urlencode($competicao['cd_competicao'] ?? '') ?>" method="post">
            <label for="nm_competicao">Nome da competição</label>
            <input type="text" id="nm_competicao" name="nm_competicao" value="<?= $valor('nm_competicao') ?>" required>

            <label for="dt_inicio">Data de início</label>
            <input type="date" id="dt_inicio" name="dt_inicio" value="<?= $data('dt_inicio') ?>">

            <label for="dt_encerramento">Data de encerramento</label>
            <input type="date" id="dt_encerramento" name="dt_encerramento" value="<?= $data('dt_encerramento') ?>">

            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="/competicoes/listar" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-laranja">Salvar alterações</button>
            </div>
        </form>
    </div>
</div>
<script src="../../js/script.js"></script>
<script src="../../js/layout.js"></script>
<script src="../../js/acessibilidade.js"></script>
</body>
</html>
