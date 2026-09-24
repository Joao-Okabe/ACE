<?php
$dados = $dados ?? [];

$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');

$usuarios = $usuarios?? [];
$times = $times ?? [];
$alunos = $alunos ?? [];
$funcoes = $funcoes ?? [];

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


    <title>Times</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>
    <app-header></app-header>

    <!-- Conteúdo -->
<div class="content">
    <div class="card form-card shadow-sm">

        <form action="/times/adicionar-integrante?id=<?= htmlspecialchars($times['cd_time'] ?? 0, ENT_QUOTES, 'UTF-8') ?>" method="POST">
            
            <input type="hidden" name="id_time" value="<?= htmlspecialchars($times['cd_time'] ?? 0, ENT_QUOTES, 'UTF-8') ?>">
            <div id="etapa1">

            <div class="mb-2">
            <div class="header-form">
                <a href="/times/visualizar" class="btn-voltar" aria-label="Voltar">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </a>

                <div>
                    <h2 class="form-title">Adicionar Integrante ao time</h2>
                    <p class="form-subtitle">Preencha os dados do integrante.</p>
                </div>
            </div>
        </div>

            <div class="form-section"> 
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="times" class="form-label">Time</label>
                        <select id="times" name="times" class="form-select form-input" disabled required>
                            <option value="<?= htmlspecialchars($times['cd_time']) ?>" >
                                <?= htmlspecialchars($times['nm_time']) ?>
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="cd_usuario" class="form-label">Aluno</label>
                        <select id="cd_usuario" name="cd_usuario" class="form-select form-input" required>
                            <option value="">Selecione</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option value="<?= (int) $aluno['cd_usuario'] ?>">
                                    <?= htmlspecialchars($aluno['nm_usuario'], ENT_QUOTES, 'UTF-8') ?>
                                    (RA: <?= htmlspecialchars($aluno['ra'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="cd_funcao_integrante" class="form-label">Função no time</label>
                        <select id="cd_funcao_integrante" name="cd_funcao_integrante" class="form-select form-input" required>
                            <option value="">Selecione</option>
                            <?php foreach ($funcoes as $funcao): ?>
                                <option value="<?= (int) $funcao['cd_funcao_integrante'] ?>">
                                    <?= htmlspecialchars($funcao['nm_funcao'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="numero_camisa" class="form-label">Número da camiseta</label>
                        <input
                            type="text"
                            id="numero_camisa"
                            name="numero_camisa"
                            class="form-control form-input"
                            inputmode="numeric"
                            pattern="[1-9][0-9]*"
                            maxlength="9"
                            placeholder="Ex.: 10"
                        >
                    </div>

                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="capitao" name="capitao" value="1">
                            <label class="form-check-label" for="capitao">Capitão</label>
                        </div>
                    </div>
                </div>
                </div>

                <div class="actions full d-flex justify-content-end gap-3 mt-4">
                    <a href="/times/visualizar?id=<?= htmlspecialchars($times['cd_time'] ?? 0, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Cancelar</a>
                    <button class="btn btn-laranja" type="submit">Vincular integrante</button>
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