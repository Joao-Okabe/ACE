<?php
$valor = static fn (string $campo): string => htmlspecialchars((string) ($time[$campo] ?? ''), ENT_QUOTES, 'UTF-8');

$usuario = $usuario ?? [];
$time = $time ?? [];
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
    <link rel="stylesheet" href="../../css/acessibilidade.css">

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
                            
                            <button type="button" class="btn btn-tecnico">
                                <i class="bi bi-person"></i>     
                                Técnico
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="integrante-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-title">Integrantes</h3>
            <a href="/times/adicionar-integrante?id=<?= urlencode((string) ($time['cd_time'] ?? 0)) ?>" class="btn btn-laranja">
            + Adicionar integrantes
            </a>
        </div>
        
        <div class="row g-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="integrante-card">

                    <div class="integrante-foto">
                        <img src="../../img/no-prof-pic.png" alt="Foto do integrante">
                    </div>

                    <div class="integrante-info">
                        <h4>Nome</h4>
                        <p class="label-info">Cargo</p>
                    </div>

                </div>
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
    <script src="../../js/acessibilidade.js"></script>

</body>
</html>