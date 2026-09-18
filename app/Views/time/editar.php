<?php
$time = $time ?? [];
$dados = array_merge($time, $dados ?? []);
$valor = static fn (string $campo): string => htmlspecialchars((string) ($dados[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
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
    <title>Editar time</title>
</head>
<body>
<app-header></app-header>
<div class="content">
    <div class="card form-card shadow-sm">
        <h2 class="form-title">Editar time</h2>
        <p class="form-subtitle">
            Altualize o nome do time.
        </p>

        <?php if (!empty($_GET['sucesso'])): ?>
            <div class="alert alert-success auth-success" role="alert" aria-live="polite">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>Time editado com sucesso.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger auth-error" role="alert" aria-live="assertive">
                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="/times/atualizar?id=<?= urlencode($time['cd_time']) ?>" method="post" class="form-grid">

        <div class="row">    
            <div class="col-12 mb-3">
            <label class="form-label" for="nm_time">Nome do time</label>
            <input type="text" class="form-control form-input" id="nm_time" name="nm_time" value="<?= $valor('nm_time') ?>" required placeholder="Digite o nome do time">
        </div>

            <div class="actions full d-flex justify-content-end gap-3 mt-4">
                <a href="/times/listar" class="btn btn-secondary">Cancelar</a>
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
