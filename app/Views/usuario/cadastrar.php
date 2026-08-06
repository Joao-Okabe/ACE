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
<body>

<div class="container-fluid p-0">
    <div class="row g-0 vh-100">
    
    
    <div class="col-lg-5 d-none d-lg-flex auth-left">
        <div class="left-content">
            <img src="../../img/logo-ace-completa.png" class="auth-logo">

            <h2 class="titulo-esquerda">
                CONECTANDO A SUA ESCOLA<br>
                AO ESPORTE!
            </h2>
        </div>

    </div>

    <div class="col-lg-7 auth-right">

        <div class="auth-card auth-card-cadastro"> 

            <h1>Cadastro</h1>

            <?php if (!empty($_GET['sucesso'])): ?>
                <p class="alert success">Usuário cadastrado com sucesso.</p>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/usuarios" method="post" enctype="multipart/form-data" class="form-grid">

        <!--Foto de perfil-->
                <div class="d-flex justify-content-center mb-2">
                    <label for="foto_perfil" class="foto-perfil" id="fotoPerfil">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                <input type="file" accept="image/*" name="foto_perfil" id="foto_perfil" hidden>
                </div>
                <div>
                    <p class="form-label">Adicionar foto de perfil</p>
                </div>

        <!--EMAIL-->
                <label for="email">E-mail</label>
                <div class="form-input-group mb-3">
                    <input type="email" class="form-control form-input" placeholder="Digite seu e-mail" id="email" name="email" value="<?= $valor('email') ?>" required>
                </div>

        <!--ESCOLA-->
                <div class="field full">
                    <label for="escola">Escola</label>
                    <select name="escola" id="escola" required>
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

                <div class="field">
                    <label >Senha</label>
                    <div class="form-input-group mb-3">
                        <input type="password" class="form-control form-input" placeholder="Digite sua senha" id="senha" name="senha" required>
                    </div>
                </div>

        <!-- Papel -->
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

        <!-- Botao cadastro -->
                <div class="actions full">
                    <button type="submit" class="btn btn-laranja w-100">Cadastrar</button>
                </div>
                
        <!--Link para login-->
                <div class="text-center mt-2">
                    <a href="../../Views/auth/login.php" class="auth-link">
                        Já possui conta? Login
                    </a>
                </div>  
            </form>
        </section>
    </main>
</body>
</html>
