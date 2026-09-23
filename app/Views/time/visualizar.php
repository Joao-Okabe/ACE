<?php
$valor = static fn (string $campo): string => htmlspecialchars((string) ($time[$campo] ?? ''), ENT_QUOTES, 'UTF-8');

$usuario = $usuario ?? [];
$time = $time ?? [];
$integrantes = $integrantes ?? [];
$responsaveis = $responsaveis ?? [];
$podeGerenciar = $podeGerenciar ?? false;
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
    <link rel="stylesheet" href="../../css/visualizar.css">


    <title>Visualizar time</title>
</head>
<body>

<app-header></app-header>

<div class="content">
    <div class="mb-2">
        <div class="header-visualizar mb-4">
            <a href="/times/listar" class="btn-voltar"><i class="bi bi-arrow-left"></i></a>
            <h2 class="form-title">Visualizar time</h2>
        </div>

    <div class="visu-box mb-4">
        <div class="row align-items-center">
            <div class="col-lg-3">
                <div class="foto">
                    <img src="<?= htmlspecialchars(upload_url($time['path_escudo'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Brasao do time">
                </div>
            </div>

            <div class="col-lg-9 d-flex align-items-center">
                <div class="d-flex justify-content-between align-items-center w-100">

                    <div class="d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center">
                            <h2 class="name me-3 mt-0"><?= $valor('nm_time') ?></h2>

                            <span class="badge-status">
                                <?= ($time['ativo'] ?? false) ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </div>

                        <div class=" mt-3">
                            <span class="label-info">
                                Principal
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4 ms-auto">
                        <div class="d-flex flex-column align-items-end gap-2">
                            
                            <button type="button" class="btn btn-partida">
                                <i class="bi bi-dribbble"></i> 
                                Partidas
                            </button>
                            
                            <?php if ($podeGerenciar): ?>
                            <a href="/times/adicionar-tecnico?id=<?= (int) ($time['cd_time'] ?? 0) ?>" class="btn btn-tecnico">
                                <i class="bi bi-person"></i>     
                                Técnico
                            </a>

                            <a href="/times/escalacao?id=<?= (int) ($time['cd_time'] ?? 0) ?>" class="btn btn-tecnico">
                                <i class="bi bi-person"></i>     
                                Escalação
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="integrante-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-title">Integrantes</h3>
            <?php if ($podeGerenciar): ?>
            <a href="/times/adicionar-integrante?id=<?= urlencode((string) ($time['cd_time'] ?? 0)) ?>" class="btn btn-laranja">
            + Adicionar integrantes
            </a>
            <?php endif; ?>
        </div>
        
        <div class="row g-4 mb-5">
            <?php foreach ($integrantes as $integrante): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="integrante-card">
                        <div class="integrante-foto">
                            <img src="<?= htmlspecialchars(upload_url($integrante['foto_perfil'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Foto do integrante">
                        </div>
                        <div class="integrante-info">
                            <h4><?= htmlspecialchars($integrante['nm_usuario'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <p class="label-info"><?= htmlspecialchars($integrante['nm_funcao_integrante'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if (!empty($integrante['numero_camisa']) || !empty($integrante['capitao']) || !empty($escalacao)): ?>p>
                                <p class="value-info">
                                    <?php if (!empty($escalacao)): ?>
                                        Escalação: <?php echo $escalacao === false ? "Reserva" : "Titular"; ?> 
                                    <?php endif; ?>
                                </p>
                                <p class="value-info">
                                    <?php if (!empty($integrante['numero_camisa'])): ?>
                                        Camisa <?= (int) $integrante['numero_camisa'] ?>
                                    <?php endif; ?>
                                </p>
                                <p class="value-info">
                                    <?php if (!empty($integrante['capitao'])): ?>
                                        Capitão
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($podeGerenciar): ?>
                            <form method="post" action="/times/remover-integrante" class="mt-2" onsubmit="return confirm('Remover este integrante do time?');">
                                <input type="hidden" name="id_time" value="<?= (int) $time['cd_time'] ?>">
                                <input type="hidden" name="cd_usuario" value="<?= (int) $integrante['cd_usuario'] ?>">
                                <button type="submit" class="btn btn-delete btn-sm" title="Remover integrante" aria-label="Remover integrante">
                                    <i class="bi bi-trash-fill"></i> Remover
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


   
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-title">Responsáveis</h3>
            <?php if ($podeGerenciar): ?>
            <a href="/times/adicionar-responsavel?id=<?= urlencode((string) ($time['cd_time'] ?? 0)) ?>" class="btn btn-laranja">
            + Adicionar responsáveis
            </a>
            <?php endif; ?>
        </div>
        
        <div class="row g-4">
            <?php foreach ($responsaveis as $responsavel): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="integrante-card">
                        <div class="integrante-foto">
                            <img src="<?= htmlspecialchars(upload_url($responsavel['foto_perfil'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Foto do responsável">
                        </div>
                        <div class="integrante-info">
                            <h4><?= htmlspecialchars($responsavel['nm_usuario'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <p class="label-info">Responsável</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
            
        </div>

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


</body>
</html>