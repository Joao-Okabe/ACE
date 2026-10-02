<?php

?>
<?php
$usuario = $usuario ?? null;
$dados = $competicao ?? [];
$periodoInscricao = $periodoInscricao ?? null;
$timesInscritos = $timesInscritos ?? [];
$timesDisponiveis = $timesDisponiveis ?? [];
$chaveamento = $chaveamento ?? []; 
$podeGerarChaveamento = $podeGerarChaveamento ?? false;
$podeGerenciar = $podeGerenciar ?? false;
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
$data = static fn (?string $valor): string => $valor ? htmlspecialchars(substr($valor, 0, 10), ENT_QUOTES, 'UTF-8') : 'Não definida';
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
    <link rel="stylesheet" href="../../css/chaveamento.css">

    <title>Visualizar competição</title>
    <link rel="icon" type="image/png" href="../../img/icon.png">
</head>
<body>
    <app-header></app-header>
    <div class="content">
    <div class="visu-box">
        <div class="competicao-titulo">
            <a href="/competicoes/listar" class="btn-voltar"><i class="bi bi-arrow-left"></i></a>
            <div class="competicao-info">
                <h2 class="name-competicao"><?= $valor('nm_competicao') ?></h2>
                <p>Etapas da Competição</p>
            </div>
        </div>
    </div>
<script>
    window.chaveamento = <?= 
        json_encode(
            $chaveamento,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        )       
    ?>
    
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
<script src="../../js/chaveamento.js"></script>

</body>
</html>