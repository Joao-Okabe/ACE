<?php
$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="page">
        <section class="form-panel auth-panel">
            <h1>Login</h1>

            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/login" method="post" class="form-grid">
                <div class="field full">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?= $valor('email') ?>" required autofocus>
                </div>

                <div class="field full">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <div class="actions full">
                    <a href="/usuarios/cadastrar" class="button secondary">Criar usuário</a>
                    <button type="submit">Entrar</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
