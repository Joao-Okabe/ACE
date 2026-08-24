<?php
$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
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
    <link rel="stylesheet" href="../../css/auth.css">

    <title>ACE - Login</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>
<a class="skip-link" href="#conteudo-principal">Pular para o conteúdo principal</a>

<div class="container-fluid p-0">
    <div class="row g-0 vh-100">
        <div class="col-lg-5 d-none d-lg-flex auth-left">

        <div class="left-content">

            <img src="../../img/logo-ace-completa.png" class="auth-logo" alt="ACE — Competições Escolares">

            <h2 class="titulo-esquerda ">
                CONECTANDO A SUA ESCOLA<br>
                AO ESPORTE!
            </h2>

        </div>
    </div>

    <main class="col-lg-7 auth-right" id="conteudo-principal">

        <div class="auth-card auth-card-login">

            <h1>Login</h1>

            <?php if (!empty($erro)): ?>
                <p class="alert error" role="alert" aria-live="assertive" tabindex="-1"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/login" method="post">
                <label for="email">E-mail <span class="required-indicator" aria-hidden="true">*</span></label>
                <div class="form-input-group mb-3">
                    <input type="email" id="email" name="email" class="form-control form-input" placeholder="Digite seu e-mail" value="<?= $valor('email') ?>" required autofocus autocomplete="username">
                </div>

                <label for="senha">Senha <span class="required-indicator" aria-hidden="true">*</span></label>
                <div class="input-group form-input-group">
                    <input type="password" id="senha" name="senha" class="form-control form-input" placeholder="Digite sua senha" autocomplete="current-password" required aria-required="true">
                    <button class="btn btn-outline-secondary" type="button" data-toggle-password="senha" aria-label="Mostrar senha"><i class="bi bi-eye" aria-hidden="true"></i></button>
                </div>

                <div class="mt-3 mb-3">
                    <button type="submit" class="btn btn-laranja w-100">Entrar</button>
                </div>

                <div class="text-center mt-2">
                    <a href="/usuarios/cadastrar" class="auth-link">Não possui conta? Cadastre-se</a>
                </div>
            </form>
        </div>
    </main>
</div>
</div>

<script src="../../js/script.js"></script>
</body>
</html>
