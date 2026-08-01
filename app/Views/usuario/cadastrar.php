<?php
$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro de usuário</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="page">
        <section class="form-panel">
            <h1>Cadastro de usuário</h1>

            <?php if (!empty($_GET['sucesso'])): ?>
                <p class="alert success">Usuário cadastrado com sucesso.</p>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/usuarios" method="post" class="form-grid">
                <div class="field full">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?= $valor('email') ?>" required>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <div class="field">
                    <label for="papel">Papel</label>
                    <select id="papel" name="papel" required>
                        <option value="">Selecione</option>
                        <option value="1" <?= $valor('papel') === '1' ? 'selected' : '' ?>>Administrador</option>
                        <option value="2" <?= $valor('papel') === '2' ? 'selected' : '' ?>>Escola</option>
                        <option value="3" <?= $valor('papel') === '3' ? 'selected' : '' ?>>Aluno</option>
                    </select>
                </div>

                <div class="actions full">
                    <a href="/escolas/cadastrar" class="button secondary">Cadastrar escola</a>
                    <a href="/login" class="button secondary">Realizar Login</a>
                    <button type="submit">Cadastrar</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
