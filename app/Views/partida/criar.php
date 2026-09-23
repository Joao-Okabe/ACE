<?php

$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
// 1 parte
$formatos = $formatos ?? [];
$esportes = $esportes ?? [];
$modalidades = $modalidades ?? [];
// 2 parte
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
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!-- Conteúdo -->
<div class="content">
    <div class="card form-card shadow-sm">

        <form action="/partidas" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_competicao" value="<?= htmlspecialchars($idCompeticao ?? 0, ENT_QUOTES, 'UTF-8') ?>">
            <div id="etapa1">
                <h4 class="form-title">Adicionar partida</h4>
                <p class="form-subtitle">
                    Preencha os dados da partida.
                </p>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="formato" class="form-label">Formato</label>

                        <select id="formato" name="formato" class="form-select form-input" required>
                            <option value="">Selecione</option>
                            <?php foreach ($formatos as $formato): ?>
                                <option value="<?= htmlspecialchars($formato['cd_formato']) ?>" <?= ($valor('formato') == $formato['cd_formato']) ? 'selected' : '' ?>><?= htmlspecialchars($formato['nm_formato']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="esporte" class="form-label">Esporte</label>

                        <select id="esporte" name="esporte" class="form-select form-input" required>
                            <option value="">Selecione</option>
                            <?php foreach ($esportes as $esporte): ?>
                                <option value="<?= htmlspecialchars($esporte['cd_esporte']) ?>" <?= ($valor('esporte') == $esporte['cd_esporte']) ? 'selected' : '' ?>><?= htmlspecialchars($esporte['nm_esporte']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="modalidade" class="form-label">Modalidade</label>
                        <select id="modalidade" name="modalidade" class="form-select form-input" required>
                            <option value="">Selecione</option>
                            <?php foreach ($modalidades as $modalidade): ?>
                                <option value="<?= htmlspecialchars($modalidade['cd_modalidade']) ?>" <?= ($valor('modalidade') == $modalidade['cd_modalidade']) ? 'selected' : '' ?>><?= htmlspecialchars(($modalidade['ds_restr_genero'] ?? '') . ' / ' . ($modalidade['ds_restr_idade'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="actions full d-flex justify-content-end gap-3 mt-4">
                    <a href="/competicoes/visualizar?id=<?= htmlspecialchars($idCompeticao ?? 0, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Cancelar</a>
                    <button class="btn btn-laranja" type="button" id="btnProximo">Próximo</button>
                </div>

            </div>

            <div id="etapa2" style="display: none;">
                <h4 class="form-title">Informações da partida</h4>
                <p class="form-subtitle">
                    Preencha os dados da partida.
                </p>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label for="timeCasa" class="form-label">Time 1</label>

                        <select id="timeCasa" name="time_casa" class="form-select form-input" required>
                            <option value="">Selecione o time</option>
                            <?php foreach ($times as $time): ?>
                                <option value="<?= htmlspecialchars($time['cd_time']) ?>" <?= ($valor('time') == $time['cd_time']) ? 'selected' : '' ?>><?= htmlspecialchars($time['nm_time']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="timeFora" class="form-label">Time 2</label>

                        <select id="timeFora" name="time_fora" class="form-select form-input" required>
                            <option value="">Selecione o time</option>
                            <?php foreach ($times as $time): ?>
                                <option value="<?= htmlspecialchars($time['cd_time']) ?>" <?= ($valor('time') == $time['cd_time']) ? 'selected' : '' ?>><?= htmlspecialchars($time['nm_time']) ?></option>
                            <?php endforeach; ?>
                        </select>

                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="dataPartida" class="form-label">Data da partida</label>
                        <input type="date" id="dataPartida" name="data_partida" class="form-control form-input" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="horaPartida" class="form-label">Horário</label>
                        <input type="time" id="horaPartida" name="hora_partida" class="form-control form-input" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="localPartida" class="form-label">Local</label>

                        <input type="text" id="localPartida" name="local_partida" class="form-control form-input"  required>
                    </div>

                </div>

                <div class="actions full d-flex justify-content-end gap-3 mt-4">

                    <button type="button" class="btn btn-secondary" id="btnVoltar">Voltar</button>
                    <button class="btn btn-laranja" type="submit">Adicionar partida</button>

                </div>

            </div>
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