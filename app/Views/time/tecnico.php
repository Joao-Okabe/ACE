<?php

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
    <link rel="stylesheet" href="../../css/lista.css">
    <link rel="stylesheet" href="../../css/acessibilidade.css">

    <title>Times</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!--Conteúdo-->  
<div class="content">  
    <div class="d-flex flex-column flex-md-row justify-content-between  mb-4">
        <div>
            <h2 class="form-title">Times</h2>
            <p class="form-subtitle">Gerencie os times da sua escola.</p>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between  mb-4">
            <a href="/times/criar" class="btn btn-laranja">
            + Adicionar time
            </a>
        </div>
    </div>
</div>

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