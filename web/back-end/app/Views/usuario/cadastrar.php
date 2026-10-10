<?php

$dados = $dados ?? [];
$escolas = $escolas ?? [];
$papeis = $papeis ?? [];
$escolaDiretor = $escolaDiretor ?? null;
$podeVincular = $podeVincular ?? false;
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


    <title>Cadastro</title>
    <link rel="icon" type="image/png" href="../../img/icon.png">
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


        <?php if ($podeVincular): ?>
        <!-- ESCOLA -->
                <div class="field full">
                    <label for="escola" class="form-label">Escola</label>
                    <?php if ($escolaDiretor !== null): ?>
                        <input type="hidden" name="escola" value="<?= (int) $escolaDiretor ?>">
                        <select class="form-select form-input" id="escola" disabled>
                            <?php foreach ($escolas as $escola): ?>
                                <?php if ((int) $escola['cd_escola'] === (int) $escolaDiretor): ?>
                                    <option selected><?= htmlspecialchars($escola['nome'] ?? $escola['nm_escola'] ?? '', ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                    <select class="form-select form-input" name="escola" id="escola" required>
                    <option value="">Selecione uma escola</option>

                    <?php foreach ($escolas as $escola): ?>
                        <option value="<?= (int) $escola['cd_escola'] ?>" <?= ((int) ($dados['escola'] ?? 0) === (int) $escola['cd_escola']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($escola['nome'] ?? $escola['nm_escola'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>

                <div class="field full">
                    <label for="papel" class="form-label">Papel</label>
                    <select class="form-select form-input" id="papel" name="papel" required>
                        <option value="">Selecione</option>
                        <?php foreach ($papeis as $papel): ?>
                            <option value="<?= htmlspecialchars($papel['codigo'], ENT_QUOTES, 'UTF-8') ?>" <?= ($dados['papel'] ?? '') === $papel['codigo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($papel['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
        <?php endif; ?>
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
