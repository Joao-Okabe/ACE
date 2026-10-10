<?php
$usuario = $usuario ?? null;
$competicao = $competicao ?? [];
$etapas = $etapas ?? [];
$tiposEtapa = (new EtapaCompeticao())->listarTipos();

$idCompeticao = (int) ($competicao['cd_competicao'] ?? 0);
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../bootstrap-icons-1.13.1/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/geral.css">
    <link rel="stylesheet" href="../../css/layout.css">

    <title>Etapas da competição</title>
    <link rel="icon" type="image/png" href="../../img/icon.png">
</head>

<body>

    <app-header></app-header>

    <div class="content">
        <div class="card form-card shadow-sm">
            <div class="mb-2">
                <div class="header-form">
                    <a href="/competicoes/visualizar?id=<?= urlencode((string) $idCompeticao) ?>" class="btn-voltar" aria-label="Voltar">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    </a>

                    <div>
                        <h2 class="form-title">Etapas da Competição</h2>
                        <p class="form-subtitle">Edite as etapas cadastradas desta competição.</p>
                    </div>

                    <a href="/competicoes/etapas/criar?id=<?= urlencode((string) $idCompeticao) ?>" class="btn btn-laranja ms-auto">
                        + Cadastrar etapa
                    </a>
                </div>
            </div>

            <?php if (!empty($_GET['sucesso'])): ?>
                <div class="alert alert-success auth-success" role="alert" aria-live="polite">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    <span>Etapa atualizada com sucesso.</span>
                </div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger auth-error" role="alert" aria-live="assertive">
                    <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if (empty($etapas)): ?>
                <div class="alert alert-info" role="alert">
                    Esta competição ainda não possui etapas cadastradas.
                </div>
            <?php else: ?>
                <div class="accordion" id="accordionEtapas">
                    <?php foreach ($etapas as $indice => $etapa):
                        $idEtapa = (int) $etapa['cd_etapa_competicao'];
                        $textoTipo = !empty($etapa['nm_tipo_etapa']) ? $etapa['nm_tipo_etapa'] : '';
                    ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="cabecalho-etapa-<?= $idEtapa ?>">
                                <button class="accordion-button <?= $indice > 0 ? 'collapsed' : '' ?>" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#etapa-<?= $idEtapa ?>"
                                    aria-expanded="<?= $indice === 0 ? 'true' : 'false' ?>"
                                    aria-controls="etapa-<?= $idEtapa ?>">
                                    <strong><?= htmlspecialchars((string) $etapa['nm_etapa'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="ms-2 text-muted">— <?= htmlspecialchars($textoTipo, ENT_QUOTES, 'UTF-8') ?> (ordem <?= (int) $etapa['ordem'] ?>)</span>
                                </button>
                            </h2>
                            <div id="etapa-<?= $idEtapa ?>" class="accordion-collapse collapse <?= $indice === 0 ? 'show' : '' ?>"
                                aria-labelledby="cabecalho-etapa-<?= $idEtapa ?>" data-bs-parent="#accordionEtapas">
                                <div class="accordion-body">
                                    <form action="/competicoes/etapas/atualizar?id=<?= urlencode((string) $idCompeticao) ?>&etapa=<?= urlencode((string) $idEtapa) ?>" method="post">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="form-label" for="nm_etapa_<?= $idEtapa ?>">Nome da etapa</label>
                                                <input class="form-control form-input" type="text" id="nm_etapa_<?= $idEtapa ?>" name="nm_etapa"
                                                    value="<?= htmlspecialchars((string) $etapa['nm_etapa'], ENT_QUOTES, 'UTF-8') ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="cd_tipo_etapa_<?= $idEtapa ?>">Tipo da etapa</label>
                                                <select class="form-select form-input" id="cd_tipo_etapa_<?= $idEtapa ?>" name="cd_tipo_etapa" required>
                                                    <option value="">Selecione o tipo...</option>
                                                    <?php foreach ($tiposEtapa as $tipo): ?>
                                                        <option value="<?= (int) $tipo['cd_tipo_etapa'] ?>"
                                                            <?= (int) $etapa['cd_tipo_etapa'] === (int) $tipo['cd_tipo_etapa'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars(!empty($tipo['ds_tipo_etapa']) ? $tipo['ds_tipo_etapa'] : $tipo['nm_tipo_etapa'], ENT_QUOTES, 'UTF-8') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="ordem_<?= $idEtapa ?>">Ordem</label>
                                                <input class="form-control form-input" type="number" id="ordem_<?= $idEtapa ?>" name="ordem" min="1"
                                                    value="<?= (int) $etapa['ordem'] ?>" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label class="form-label" for="descricao_<?= $idEtapa ?>">Descrição</label>
                                                <textarea class="form-control form-input" id="descricao_<?= $idEtapa ?>" name="descricao" rows="2"><?= htmlspecialchars((string) ($etapa['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end gap-3">
                                            <a href="/competicoes/visualizar?id=<?= urlencode((string) $idCompeticao) ?>" class="btn btn-secondary">Cancelar</a>
                                            <button type="submit" class="btn btn-laranja">Salvar alterações</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script src="../../bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

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