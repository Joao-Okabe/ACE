<?php
$usuario = $usuario ?? null;
$competicao = $competicao ?? [];
$dados = $dados ?? [];
$tiposEtapa = (new EtapaCompeticao())->listarTipos();

$valor = static fn(string $campo): string => htmlspecialchars((string) ($dados[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
$idCompeticao = (int) ($competicao['cd_competicao'] ?? 0);
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

    <title>Adicionar etapa</title>
    <link rel="icon" type="image/png" href="../../img/icon.png">
</head>

<body>
    <app-header></app-header>

    <div class="content">
        <div class="card form-card shadow-sm">

            <form action="/competicoes/etapas?id=<?= urlencode((string) $idCompeticao) ?>" method="POST">
                <div class="mb-2">
                    <div class="header-form">
                        <a href="/competicoes/etapas/editar?id=<?= urlencode((string) $idCompeticao) ?>" class="btn-voltar" aria-label="Voltar">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        </a>

                        <div>
                            <h2 class="form-title">Adicionar Etapa à Competição</h2>
                            <p class="form-subtitle">Preencha os dados da etapa.</p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger auth-error" role="alert" aria-live="assertive">
                        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                        <span><?= htmlspecialchars((string) $erro, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <div class="form-section">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="competicao" class="form-label">Competição</label>
                            <select id="competicao" class="form-select form-input" disabled required>
                                <option value="<?= $idCompeticao ?>">
                                    <?= htmlspecialchars((string) ($competicao['nm_competicao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="nm_etapa" class="form-label">Nome da etapa</label>
                            <input type="text" id="nm_etapa" name="nm_etapa" class="form-control form-input"
                                value="<?= $valor('nm_etapa') ?>" required placeholder="Ex.: Fase de grupos">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="cd_tipo_etapa" class="form-label">Tipo da etapa</label>
                            <select id="cd_tipo_etapa" name="cd_tipo_etapa" class="form-select form-input" required>
                                <option value="">Selecione</option>
                                <?php foreach ($tiposEtapa as $tipo): ?>
                                    <option value="<?= (int) $tipo['cd_tipo_etapa'] ?>"
                                        <?= ((int) ($dados['cd_tipo_etapa'] ?? 0) === (int) $tipo['cd_tipo_etapa']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(!empty($tipo['ds_tipo_etapa']) ? $tipo['ds_tipo_etapa'] : $tipo['nm_tipo_etapa'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="ordem" class="form-label">Ordem</label>
                            <input type="number" id="ordem" name="ordem" class="form-control form-input" min="1"
                                value="<?= $valor('ordem') !== '' ? $valor('ordem') : '1' ?>" required>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <input type="text" id="descricao" name="descricao" class="form-control form-input"
                                value="<?= $valor('descricao') ?>" placeholder="Descrição da etapa (opcional)">
                        </div>
                    </div>
                </div>

                <div class="actions full d-flex justify-content-end gap-3 mt-4">
                    <a href="/competicoes/etapas/editar?id=<?= urlencode((string) $idCompeticao) ?>" class="btn btn-secondary">Cancelar</a>
                    <button class="btn btn-laranja" type="submit">Cadastrar etapa</button>
                </div>
            </form>

        </div>
    </div>

    <script>
        window.usuarioLogado = {
            nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>,
            email: <?= json_encode($usuario['email'] ?? '—') ?>,
            foto: <?= json_encode(upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg')) ?>
        };
    </script>

    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>

</body>

</html>