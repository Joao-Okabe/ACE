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
    <link rel="stylesheet" href="../../css/acessibilidade.css">

    <title>Login</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body class="cor">

<div class="container-auth">
    <div class="auth-card auth-card-login">

        <div class="auth-card-left">
            <img src="../../img/logo-ace-completa.png" class="auth-logo" alt="ACE!">
            <h5>
                CONECTANDO A SUA ESCOLA<br>
                AO ESPORTE!
            </h5>
        </div>

    <div class="auth-card-right">
        <div class="mb-4">
            <h1>Login</h1>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger auth-error" role="alert" aria-live="assertive">
                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

            <form action="/login" method="post">
                <label for="email">E-mail</label>
                <div class="form-input-group mb-3">
                    <input type="email" id="email" name="email" class="form-control form-input" placeholder="Digite seu e-mail" value="<?= $valor('email') ?>" required autofocus autocomplete="username">
                </div>

                <label for="senha">Senha</label>
                <div class="form-input-group">
                    <input type="password" id="senha" name="senha" class="form-control form-input" placeholder="Digite sua senha" autocomplete="current-password" required>
                    <button type="button" id="toggle-senha" class="btn-toggle-password" title="Mostrar senha">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="mt-3 mb-3">
                    <button type="submit" class="btn btn-laranja w-100">Entrar</button>
                </div>

                <div class="text-center mt-2">
                    <a href="/usuarios/cadastrar" class="auth-link">Não possui conta? Cadastre-se</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>


<script src="../../js/script.js"></script>
<script src="../../js/acessibilidade.js"></script>

<!--Mostrar senha-->
<script>
    const senha = document.getElementById('senha');
    const toggleSenha = document.getElementById('toggle-senha');

    toggleSenha.addEventListener('click', function () {
        const senhaVisivel = senha.type === 'text';

        senha.type = senhaVisivel ? 'password' : 'text';

        this.setAttribute(
            'aria-label',
            senhaVisivel ? 'Mostrar senha' : 'Ocultar senha'
        );

        this.setAttribute(
            'title',
            senhaVisivel ? 'Mostrar senha' : 'Ocultar senha'
        );

        const icone = this.querySelector('i');

        icone.classList.toggle('bi-eye', senhaVisivel);
        icone.classList.toggle('bi-eye-slash', !senhaVisivel);
    });
</script>

</body>
</html>