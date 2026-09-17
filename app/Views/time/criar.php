<?php

$usuario = $usuario ?? null;
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
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/acessibilidade.css">

    <title>Criar Time</title>
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>
<!--Conteúdo-->
<div class="content">
     <div class="card form-card shadow-sm">
        <div class="mb-2">
            <div>
                <h2 class="form-title">Cadastrar Time</h2>
                <p class="form-subtitle">
                Preencha os dados da competição para realizar o cadastro.
                </p>
            </div>
        </div>
    
        <?php if (!empty($_GET['sucesso'])): ?>
            <p class="alert success">Time cadastrado com sucesso.</p>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

    <form action="/times" method="POST" enctype="multipart/form-data">
        <!-- Foto de perfil -->
        <div class="perfil-aluno mb-4">
            <label for="path_brasao" class="foto-perfil" id="path_brasao">
                <i class="bi bi-camera-fill"></i>
            </label>
            <div class="text-perfil">
                <p class="form-label">Adicionar imagem do brasão</p>
            </div>
            <input type="file" accept="image/*" name="path_brasao" id="path_brasao">
        </div>

        <label for="nm_time">Nome do time</label>
        <input type="text" id="nm_time" value="<?= $valor('nm_time') ?>" name="nm_time" required>

        <button type="submit">Cadastrar time</button>
    </form>

    <script>
        window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
        email: <?= json_encode($usuario['email'] ?? '—') ?>, 
        foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };
    </script>

    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>
    <script src="../../js/acessibilidade.js"></script>

</body>
</html>