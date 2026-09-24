<?php
$times = $times ?? [];
$usuarios = $usuarios ?? [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/geral.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/lista.css">
    <link rel="stylesheet" href="../../css/acessibilidade.css">
    <title>Adicionar técnico</title>
</head>
<body>
    <app-header></app-header>

    <main class="content">
        <div class="card form-card shadow-sm">
            <form action="/times/adicionar-tecnico?id=<?= (int) ($times['cd_time'] ?? 0) ?>" method="post">
                <input type="hidden" name="id_time" value="<?= (int) ($times['cd_time'] ?? 0) ?>">

        <div class="mb-2">
            <div class="header-form">
                <a href="/times/listar" class="btn-voltar" aria-label="Voltar">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </a>

                <div>
                    <h2 class="form-title">Adicionar técnico ao time</h2>
                    <p class="form-subtitle">Selecione um usuário para vincular como técnico.</p>
                </div>
            </div>
        </div>

            <div class="form-section"> 
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="time" class="form-label">Time</label>
                        <input id="time" class="form-control form-input" value="<?= htmlspecialchars($times['nm_time'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="cd_usuario" class="form-label">Usuário</label>
                        <select id="cd_usuario" name="cd_usuario" class="form-select form-input" required>
                            <option value="">Selecione</option>
                            <?php foreach ($usuarios as $usuarioDisponivel): ?>
                                <option value="<?= (int) $usuarioDisponivel['cd_usuario'] ?>">
                                    <?= htmlspecialchars($usuarioDisponivel['nm_usuario'], ENT_QUOTES, 'UTF-8') ?>
                                    (<?= htmlspecialchars($usuarioDisponivel['email'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

                <div class="actions full d-flex justify-content-end gap-3 mt-4">
                    <a href="/times/visualizar?id=<?= (int) ($times['cd_time'] ?? 0) ?>" class="btn btn-secondary">Cancelar</a>
                    <button class="btn btn-laranja" type="submit">Vincular técnico</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        window.usuarioLogado = {
            nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>,
            email: <?= json_encode($usuario['email'] ?? '—') ?>,
            foto: <?= json_encode(upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg')) ?>
        };
    </script>
    <script src="../../js/layout.js"></script>
    <script src="../../js/acessibilidade.js"></script>
</body>
</html>
