<?php

$dados = $dados ?? [];
$valor = function ($campo) use ($dados) {
    return htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
};

?>
<!doctype html>
<html lang="pt-BR">
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


    <title>ACE - Cadastro</title>
</head>
<body class="cor"> 

<div class="container-auth">
    <div class="auth-card auth-card-cadastro">
    
        <div class="auth-card-left">
            <img src="../../img/logo-ace-completa.png" class="auth-logo" alt="ACE!">
            <h5>
                CONECTANDO A SUA ESCOLA<br>
                AO ESPORTE!
            </h5>
        </div>

    <div class="auth-card-right">
        <div class="mb-4">
            <h1>Cadastro</h1>
        </div>

            <?php if (!empty($_GET['sucesso'])): ?>
                <div class="alert alert-success auth-success" role="alert" aria-live="polite">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    <span>Usuário cadastrado com sucesso.</span>
                </div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger auth-error" role="alert" aria-live="assertive">
                    <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form action="/usuarios" method="post" enctype="multipart/form-data">


        <!-- NOME -->
            <div class="mb-3">
                <label for="nm_usuario">Nome</label>
                <div class="form-input-group">
                    <input type="text" class="form-control form-input" placeholder="Digite seu nome" id="nm_usuario" name="nm_usuario" value="<?= $valor('nm_usuario') ?>" required>
                </div>
            </div>

        <!--EMAIL-->
            <div class="mb-3">
                <label for="email">E-mail</label>
                <div class="form-input-group">
                    <input type="email" class="form-control form-input" placeholder="Digite seu e-mail" id="email" name="email" value="<?= $valor('email') ?>" required>
                </div>
            </div>

        <!--ESCOLA-->
            <!-- 
                <div class="field full">
                    <label for="escola" class="form-label">Escola</label>
                    <select class="form-select form-input" name="escola" id="escola" required>
                    <option value="">Selecione uma escola</option>

                    <?php if (!empty($escolas)): ?>
                    <?php foreach ($escolas as $escola): ?>
                        <option value="<?= $escola['cd_escola'] ?>">
                            <?= htmlspecialchars($escola['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </select>
                </div>
            -->
        <!--SENHA-->
            <div class="mb-3">
                    <label for="senha">Senha</label>
                    <div class="form-input-group">
                        <input type="password" class="form-control form-input" placeholder="Digite sua senha" id="senha" name="senha" required>
                        <button type="button" id="toggle-senha" class="btn-toggle-password" title="Mostrar senha">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                    </div>
            </div>

        <!-- Papel -->
                <!-- 
                <div class="field">
                    <label for="papel">Papel</label>
                    <div class="form-input-group mb-3">
                        <select class="form-select form-input" id="papel" name="papel" required>
                            <option selected disabled value="">Selecione</option>
                            <option value="1" <?= $valor('papel') === '1' ? 'selected' : '' ?>>Administrador</option>
                            <option value="2" <?= $valor('papel') === '2' ? 'selected' : '' ?>>Professor</option>
                            <option value="3" <?= $valor('papel') === '3' ? 'selected' : '' ?>>Secretaria</option>
                            <option value="4" <?= $valor('papel') === '4' ? 'selected' : '' ?>>Coordenador Escolar</option>
                            <option value="5" <?= $valor('papel') === '5' ? 'selected' : '' ?>>Diretor</option>
                        </select>
                    </div>
                </div>
                 -->
        <!-- Botao cadastro -->
                <div class="mt-4 mb-3">
                    <button type="submit" class="btn btn-laranja w-100">Cadastrar</button>
                </div>
                
        <!--Link para login-->
                <div class="text-center mt-2">
                    <a href="/login" class="auth-link">
                        Já possui conta? Login
                    </a>
                </div>  
            </form>
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
