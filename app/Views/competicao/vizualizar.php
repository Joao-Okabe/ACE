<?php
$usuario = $usuario ?? null;
$dados = $competicao ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/geral.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/visualizar.css">

    <title>Visualizar competição</title>
</head>
<body>
    
<app-header></app-header>
<div class="content">

<div class="visu-box">
            <div class="competicao-titulo">
                <a href="/competicoes/listar" class="btn-voltar"><i class="bi bi-arrow-left"></i></a>
                <div class="competicao-info">
                    <h2 class="name-competicao"><?= $valor('nm_competicao') ?></h2>
                    <p>Competição escolar</p>
                </div>
            </div>

    <!-- Botões -->
    <div class="competicao-tabs" role="tablist">

        <button type="button" class="competicao-tab active" data-tab="local">
            <i class="bi bi-geo-alt-fill"></i>
            Local
        </button>

        <button type="button" class="competicao-tab" data-tab="data">
            <i class="bi bi-calendar-event"></i>
            Data
        </button>

        <button type="button" class="competicao-tab" data-tab="times">
            <i class="bi bi-people-fill"></i>
            Times
        </button>

        <button type="button" class="competicao-tab" data-tab="arbitros">
            <i class="bi bi-person-badge"></i>
            Árbitros
        </button>

        <button type="button" class="competicao-tab" data-tab="partidas">
            <i class="bi bi-dribbble"></i>
            Partidas
        </button>

        <button
            type="button" class="competicao-tab" data-tab="chaveamento">
            <i class="bi bi-diagram-3"></i>
            Chaveamento
        </button>
    </div>


    <!-- Conteúdo -->
    <div class="competicao-conteudo">

        <!-- Local -->
        <div class="competicao-painel active" id="local">
            <h3>Local da competição</h3>

            <div class="info-grid">
                <div class="info-item">
                    <h6>Local</h6>
                    <p><?= $valor('local') ?></p>
                </div>
            </div>
        </div>

        <!-- Data -->
        <div class="competicao-painel" id="data">
            <h3>Datas da competição</h3>

            <div class="info-grid">
                <div class="info-item">
                    <h6>Data de início</h6>
                    <p><?= htmlspecialchars(substr((string) ($competicao['inicio_em'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="info-item">
                    <h6>Data de encerramento</h6>
                    <p><?= htmlspecialchars(substr((string) ($competicao['fim_em'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>

        <!-- Times -->
        <div class="competicao-painel" id="times">
            <h3>Times participantes</h3>
            <p>Informações dos times aparecerão aqui.</p>
        </div>


        <!-- Árbitros -->
        <div class="competicao-painel" id="arbitros">
            <h3>Árbitros</h3>
            <p>Informações dos árbitros aparecerão aqui.</p>
        </div>

        <!-- Partidas -->
        <div class="competicao-painel" id="partidas">
            <h3>Partidas</h3>
            <div class="partidas-header">
            <p>Confira as partidas desta competição ou adicione novas partidas.</p>
            <a href="/partidas/criar?id_competicao=<?= urlencode($dados['cd_competicao'] ?? '') ?>" class="btn btn-laranja">
            + Adicionar partidas
            </a>
        </div>

        
        <div class="row g-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="partida-card">

                    <div class="partida-info">
                        <h4>Time A × Time B</h4>
                        <p>
                        <i class="bi bi-calendar-event"></i>
                        20/09/2026
                        </p>
                        <p>
                        <i class="bi bi-clock"></i>
                        14:00
                        </p>
                    </div>
                </div>
            </div>  
        </div>
            
        </div>

        <!-- Chaveamento -->
        <div class="competicao-painel" id="chaveamento">
            <h3>Chaveamento</h3>
            <p>O chaveamento da competição aparecerá aqui.</p>
        </div>

    </div>

</div>


</div>

<script>

window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
email: <?= json_encode($usuario['email'] ?? '—') ?>, 
foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };

document.querySelectorAll('.competicao-tab').forEach(botao => {

    botao.addEventListener('click', () => {

        const tab = botao.dataset.tab;

        // Remove ativo dos botões
        document.querySelectorAll('.competicao-tab').forEach(btn => {
            btn.classList.remove('active');
        });

        // Remove ativo dos conteúdos
        document.querySelectorAll('.competicao-painel').forEach(painel => {
            painel.classList.remove('active');
        });

        // Ativa botão clicado
        botao.classList.add('active');

        // Mostra conteúdo correspondente
        document.getElementById(tab).classList.add('active');
    });

});
</script>

<script src="../../js/script.js"></script>
<script src="../../js/layout.js"></script>

</body>
</html>