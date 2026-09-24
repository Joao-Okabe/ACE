<?php
$usuario = $usuario ?? null;
$dados = $competicao ?? [];
$periodoInscricao = $periodoInscricao ?? null;
$timesInscritos = $timesInscritos ?? [];
$timesDisponiveis = $timesDisponiveis ?? [];
$chaveamento = $chaveamento ?? []; 
$podeGerarChaveamento = $podeGerarChaveamento ?? false;
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

        <button type="button" class="competicao-tab active" data-tab="inscricao">
            <i class="bi bi-file-earmark-medical"></i>
Inscrição
        </button>

        <button type="button" class="competicao-tab" data-tab="localedata">
            <i class="bi bi-geo-alt-fill"></i>
            Local e Data
        </button>

        <button type="button" class="competicao-tab" data-tab="times">
            <i class="bi bi-people-fill"></i>
            Times
        </button>

        <button type="button" class="competicao-tab" data-tab="arbitros">
            <i class="bi bi-person-badge"></i>
            Árbitros
        </button>

        <button
            type="button" class="competicao-tab" data-tab="chaveamento">
            <i class="bi bi-diagram-3"></i>
            Chaveamento
        </button>

        <button type="button" class="competicao-tab" data-tab="partidas">
            <i class="bi bi-dribbble"></i>
            Partidas
        </button>
    </div>


    <!-- Conteúdo -->
    <div class="competicao-conteudo">

        <!-- Local Data -->
        <div class="competicao-painel" id="localedata">
            <h3>Local e Datas da competição</h3>

            <div class="info-grid">
                <div class="info-item">
                    <h6>Data de início</h6>
                    <p><?= htmlspecialchars(substr((string) ($competicao['inicio_em'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="info-item">
                    <h6>Data de encerramento</h6>
                    <p><?= htmlspecialchars(substr((string) ($competicao['fim_em'] ?? ''), 0, 10), ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="info-item">
                    <h6>Local</h6>
                    <p><?= $valor('local') ?></p>
                </div>
            </div>
        </div>

        <!-- Árbitros -->
        <div class="competicao-painel" id="arbitros">
            <h3>Árbitros</h3>
            <p>Informações dos árbitros aparecerão aqui.</p>
        </div>

        <!-- Chaveamento -->
        <div class="competicao-painel" id="chaveamento">
            <div class="chaveamento-header">
                <div>
                    <h3>Chaveamento</h3>
                    <p>O chaveamento da competição aparecerá aqui.</p>
                </div>

                <?php if ($podeGerarChaveamento && empty($chaveamento)): ?>
                    <form action="/competicoes/gerar-chaveamento" method="post">
                        <input type="hidden" name="id_competicao" value="<?= (int) ($dados['cd_competicao'] ?? 0) ?>">
                        <button type="submit" class="btn btn-laranja">
                            <i class="bi bi-diagram-3"></i>
                            Gerar chaveamento
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (!empty($_GET['chaveamento_gerado'])): ?>
                <div class="alert alert-success">Chaveamento gerado com sucesso.</div>
            <?php endif; ?>

            <?php if (!empty($_GET['erro_chaveamento'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro_chaveamento'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="chaveamento" id="chaveamento-container"></div>
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
        </div>

        <!-- Inscrição -->
        <div class="competicao-painel active" id="inscricao">
            <h3>Inscrição</h3>

            <?php if (!empty($_GET['sucesso_inscricao'])): ?>
                <div class="alert alert-success">Período de inscrição cadastrado com sucesso.</div>
            <?php endif; ?>

            <?php if (!empty($_GET['time_inscrito'])): ?>
                <div class="alert alert-success">Time inscrito com sucesso.</div>
            <?php endif; ?>

            <?php if (!empty($_GET['time_removido'])): ?>
                <div class="alert alert-success">Inscrição removida com sucesso.</div>
            <?php endif; ?>

            <?php if (!empty($_GET['erro_inscricao'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro_inscricao'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="partidas-header">
                <p>
                    <?php if ($periodoInscricao !== null): ?>
                        Inscrições de <?= $data($periodoInscricao['dt_inicio_inscricao'] ?? null) ?>
                        até <?= $data($periodoInscricao['dt_encerramento_inscricao'] ?? null) ?>
                    <?php else: ?>
                        Cadastre um período de inscrição na edição da competição.
                    <?php endif; ?>
                </p>
                <a href="/competicoes/editar?id=<?= urlencode($dados['cd_competicao'] ?? '') ?>" class="btn btn-laranja">
                    Editar período
                </a>
            </div>

            <?php if ($periodoInscricao !== null): ?>
                <form action="/competicoes/inscrever-time" method="post" class="row g-3 align-items-end mb-4">
                    <input type="hidden" name="id_competicao" value="<?= (int) ($dados['cd_competicao'] ?? 0) ?>">

                    <div class="col-md-8">
                        <label class="form-label" for="id_time">Time</label>
                        <select id="id_time" name="id_time" class="form-select form-input" required>
                            <option value="">Selecione um time</option>
                            <?php foreach ($timesDisponiveis as $timeDisponivel): ?>
                                <option value="<?= (int) $timeDisponivel['cd_time'] ?>">
                                    <?= htmlspecialchars($timeDisponivel['nm_time'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-laranja w-100" <?= empty($timesDisponiveis) ? 'disabled' : '' ?>>
                            Inscrever time
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="row g-4">
                <?php if (empty($timesInscritos)): ?>
                    <div class="col-12">
                        <p>Nenhum time inscrito até o momento.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($timesInscritos as $time): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="partida-card">
                            <div class="partida-info">
                                <div class="tc list-perfil">
                                    <img src="<?= htmlspecialchars(upload_url($time['path_escudo'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Escudo do time">
                                </div>
                                <h4><?= htmlspecialchars($time['nm_time'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p>
                                    <i class="bi bi-calendar-check"></i>
                                    <?= $data($time['inscrito_em'] ?? null) ?>
                                </p>
                                <form action="/competicoes/remover-time-inscrito" method="post" class="mt-3">
                                    <input type="hidden" name="id_competicao" value="<?= (int) ($dados['cd_competicao'] ?? 0) ?>">
                                    <input type="hidden" name="id_time" value="<?= (int) $time['cd_time'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">Remover</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


        <!-- Time -->
        <div class="competicao-painel" id="times">
            <h3>Times Inscritos</h3>
            <div class="partidas-header">
                <p>Confira os times inscritos desta competição.</p>
            </div>
                <?php foreach ($timesInscritos as $time): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="partida-card">
                            <div class="partida-info">
                                <div class="tc list-perfil">
                                    <img src="<?= htmlspecialchars(upload_url($time['path_escudo'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Escudo do time">
                                </div>
                                <h4><?= htmlspecialchars($time['nm_time'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p>
                                    <i class="bi bi-calendar-check"></i>
                                    <?= $data($time['inscrito_em'] ?? null) ?>
                                </p>
                                <form action="/competicoes/remover-time-inscrito" method="post" class="mt-3">
                                    <input type="hidden" name="id_competicao" value="<?= (int) ($dados['cd_competicao'] ?? 0) ?>">
                                    <input type="hidden" name="id_time" value="<?= (int) $time['cd_time'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">Remover</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
        </div>
    </div>

</div>
<script>
window.chaveamento = <?= json_encode(
    $chaveamento,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) ?>;
</script>
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
<script src="../../js/chaveamento.js"></script>

</body>
</html>