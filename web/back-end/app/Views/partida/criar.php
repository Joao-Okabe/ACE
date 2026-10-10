<?php

$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');

$times = $times ?? [];

$idPartida = $idPartida ?? [];

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
    <link rel="stylesheet" href="../../css/layout.css">
 

    <title>Adicionar partida</title>
    <link rel="icon" type="image/png" href="../../img/icon.png">
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!-- Conteúdo -->
<div class="content">
    <div class="card form-card shadow-sm">

        <form action="/partidas" method="POST" enctype="multipart/form-data">

        </form>

    </div>
</div>

<script>
    const etapa1 = document.getElementById('etapa1');
    const etapa2 = document.getElementById('etapa2');

    const btnProximo = document.getElementById('btnProximo');
    const btnVoltar = document.getElementById('btnVoltar');

    btnProximo.addEventListener('click', function () {

        const formato = document.getElementById('formato').value;
        const esporte = document.getElementById('esporte').value;
        const modalidade = document.getElementById('modalidade').value;

        if (!formato || !esporte || !modalidade) {
            alert('Preencha todos os campos antes de continuar.');
            return;
        }

        etapa1.style.display = 'none';
        etapa2.style.display = 'block';

        document.getElementById('timeCasa').focus();

    });

    btnVoltar.addEventListener('click', function () {

        etapa2.style.display = 'none';
        etapa1.style.display = 'block';

        btnProximo.focus();

    });

    window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
    email: <?= json_encode($usuario['email'] ?? '—') ?>, 
    foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };

    </script>

    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>

</body>
</html>