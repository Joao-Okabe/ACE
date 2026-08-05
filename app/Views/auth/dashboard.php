<?php
$usuario = $usuario ?? null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="page">
            <h1>Dashboard</h1>
            <?php if ($usuario !== null): ?>
                <p class="session-info">
                    <?= htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p class="session-info">
                    Perfil: <?= htmlspecialchars(implode(', ', $usuario['papeis'] ?? []), ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php else: ?>
                <p class="alert error">Sessão inválida.</p>
            <?php endif; ?>

            <div class="actions full">
                <a href="/escolas/listar" class="button secondary">Listar escolas</a>
                <a href="/alunos/listar" class="button secondary">Listar alunos</a>
                <a href="/logout" class="button">Sair</a>
            </div>
    </main>
</body>
</html>
