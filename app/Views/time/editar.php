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

    <title>Editar time</title>
</head>
<body>
<app-header></app-header>
<div class="content">
    <div class="card form-card shadow-sm">
        <div class="mb-2">
            <div class="header-form">
                <a href="/times/listar" class="btn-voltar" aria-label="Voltar">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </a>

                <div>
                    <h2 class="form-title">Editar Time</h2>
                    <p class="form-subtitle">Altere os dados do time.</p>
                </div>
            </div>
        </div>

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

        <form action="/times/atualizar?id=<?= urlencode($time['cd_time']) ?>" method="post" enctype="multipart/form-data" class="form-grid">
           <div class="form-section"> 

            <div class="dados-iniciais"> 

            <div class="perfil-aluno">
                <label for="path_escudo" class="foto-perfil" id="fotoPerfil">
                <?php if (!empty($time['path_escudo'])): ?>
                    <img src="<?= htmlspecialchars(upload_url($dados['path_escudo'] ?? '/img/escudo.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Escudo atual do time" class="mb-2 d-block" style="width: 96px; height: 96px; object-fit: contain;">
                <?php else: ?>
                    <i class="bi bi-shield-shaded" aria-hidden="true"></i>
                <?php endif; ?>
                </label>
                <input type="file" class="form-control form-input" id="path_escudo" name="path_escudo" accept="image/jpeg,image/png,image/webp" hidden>
                <div class="text-perfil">
                <p class="form-label">Editar escudo</p>
                </div>
            </div>

        
            <div class="campo-inicial">
            <label class="form-label" for="nm_time">Nome do time</label>
            <input type="text" class="form-control form-input" id="nm_time" name="nm_time" value="<?= $valor('nm_time') ?>" required placeholder="Digite o nome do time">
            </div>
        </div>
    
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

</body>
</html>
