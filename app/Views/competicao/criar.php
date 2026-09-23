<?php

$usuario = $usuario ?? null;
$dados = $dados ?? [];

$formatos = $formatos ?? [];
$esportes = $esportes ?? [];
$modalidades = $modalidades ?? [];

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


    <title>Criar Competição</title>
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!--Conteúdo-->
<div class="content">
     <div class="card form-card shadow-sm">
        <div class="mb-2">
            <div>
                <h2 class="form-title">Cadastrar Competição</h2>
                <p class="form-subtitle">
                Preencha os dados da competição para realizar o cadastro.
                </p>
            </div>
        </div>
    
        <?php if (!empty($_GET['sucesso'])): ?>
            <div class="alert alert-success auth-success" role="alert" aria-live="polite">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>Competição cadastrada com sucesso.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger auth-error" role="alert" aria-live="assertive">
                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

    <form action="/competicoes" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label" for="nm_competicao">Nome da competição</label>
                <input type="text" class="form-control form-input" id="nm_competicao" value="<?= $valor('nm_competicao') ?>" name="nm_competicao" required placeholder="Digite o nome da competição">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="formato" class="form-label">Formato</label>

                <select id="cd_formato" name="cd_formato" class="form-select form-input" required>
                    <option value="">Selecione</option>
                    <?php foreach ($formatos as $formato): ?>
                        <option value="<?= htmlspecialchars($formato['cd_formato']) ?>" <?= ($valor('cd_formato') == $formato['cd_formato']) ? 'selected' : '' ?>><?= htmlspecialchars($formato['nm_formato']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="esporte" class="form-label">Esporte</label>

                <select id="cd_esporte" name="cd_esporte" class="form-select form-input" required>
                    <option value="">Selecione</option>
                    <?php foreach ($esportes as $esporte): ?>
                        <option value="<?= htmlspecialchars($esporte['cd_esporte']) ?>" <?= ($valor('cd_esporte') == $esporte['cd_esporte']) ? 'selected' : '' ?>><?= htmlspecialchars($esporte['nm_esporte']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="modalidade" class="form-label">Modalidade</label>
                <select id="cd_modalidade" name="cd_modalidade" class="form-select form-input" required>
                    <option value="">Selecione</option>
                    <?php foreach ($modalidades as $modalidade): ?>
                        <option value="<?= htmlspecialchars($modalidade['cd_modalidade']) ?>" 
                        <?= ($valor('cd_modalidade') == $modalidade['cd_modalidade']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(($modalidade['sexo'] ?? '') . ' / ' . ($modalidade['categoria'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="dt_inicio">Data de início</label>
                <input type="date" class="form-control form-input" id="dt_inicio" value="<?= $valor('dt_inicio') ?>" name="dt_inicio">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="dt_encerramento">Data de encerramento</label>
                <input type="date" class="form-control form-input" id="dt_encerramento" value="<?= $valor('dt_encerramento') ?>" name="dt_encerramento">
            </div>

            <div class="actions full d-flex justify-content-end gap-3 mt-4">
                <a href="/competicoes/listar" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-laranja" type="submit">Cadastrar competição</button>
            </div>
        </div>
    </form>
    </div>
</div>

    <script>
        window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
        email: <?= json_encode($usuario['email'] ?? '—') ?>, 
        foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };
    </script>

    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>

</body>
</html>