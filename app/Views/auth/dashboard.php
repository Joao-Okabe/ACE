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
        <section class="form-panel auth-panel">
            <h1>Dashboard</h1>

            <p class="session-info">
                <?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p class="session-info">
                Perfil: <?= htmlspecialchars(implode(', ', $usuario['papeis']), ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="actions full">
                <a href="/escolas/cadastrar" class="button secondary">Cadastrar escola</a>
                <a href="/logout" class="button">Sair</a>
            </div>
        </section>
        <h2>Escolas cadastradas</h2>
        <table>
            <tr>
                <th>Nome</th>
                <th>Categoria</th>
            </tr>
            <?php foreach ($escolas as $escola): ?>
                <tr>
                    <td><?= htmlspecialchars($escola['nome']) ?></td>
                    <td><?= htmlspecialchars($escola['categoria_administrativa']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
