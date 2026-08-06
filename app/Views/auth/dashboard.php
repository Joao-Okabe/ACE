<?php
$usuario = $usuario ?? null;
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
    <link rel="stylesheet" href="../../css/painel.css">

    <title>Painel</title>
</head>
<body>
<!-- NAVBAR -->
    <nav class="navbar">
        <button id="menu-btn">
            <i class="bi bi-layout-sidebar"></i>
        </button>

        <div class="logo">
            <img src="../../img/logo-ace-laranja.png" alt="logo ace">
        </div>

        <div class="perfil">
            <i class="bi bi-bell notificacao"></i>
            <img src="img/perfil.jpg" alt="Perfil">

            <div class="usuario">
                <?php if ($usuario !== null): ?>
                <span class="session-info">
                    <?= htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>

                <span class="session-info">
                    Perfil: <?= htmlspecialchars(implode(', ', $usuario['papeis'] ?? []), ENT_QUOTES, 'UTF-8') ?>
                </span>
                <?php else: ?>
                    <p class="alert error">Sessão inválida.</p>
                <?php endif; ?>
            </div>

            <i class="bi bi-chevron-down"></i>
        </div>
    </nav>
<!-- SIDEBAR -->
    <div class="sidebar">
        <div class="menu">

            <a href="./../auth/painel.html" class="menu-item active">
                <i class="bi bi-house-door-fill"></i>
                <span>Painel</span>
            </a>

            <a href="/escolas/listar" class="menu-item">
                <i class="bi bi-bank2"></i>
                <span>Escolas</span>
            </a>

            <a href="/alunos/listar" class="menu-item">
                <i class="bi bi-person-fill"></i>
                <span>Alunos</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-people-fill"></i>
                <span>Times</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-trophy-fill"></i>
                <span>Competições</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-dribbble"></i>
                <span>Partidas</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Rankings</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-gear-fill"></i>
                <span>Configurações</span>
            </a>

            <a href="..." class="menu-item">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sair da conta</span>
            </a>

        </div>
    </div>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="form-title">Bem-vindo, Gestão!</h1>
            <p class="form-subtitle">
                Confira o resumo das competições.
            </p>
        </div>

        <div class="text-end">
            <span class="dashboard-date">
                05 de Agosto de 2026 • 14:32
            </span>
        </div>

    </div>


    <!-- Cards -->
    <div class="row g-4 mb-5">

        <div class="col-lg-3 col-md-6">
            <div class="card-info">
                <div class="icon-square">
                    <i class="bi bi-dribbble"></i>
                </div>

                <div>
                    <h6>Jogos Hoje</h6>
                    <h2>0</h2>
                </div>
            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="card-info">
                <div class="icon-square">
                    <i class="bi bi-trophy-fill"></i>
                </div>

                <div>
                    <h6>Competições</h6>
                    <h2>0</h2>
                </div>
            </div>
        </div>

<!-- QTD DE TIMES-->
        <div class="col-lg-3 col-md-6">
            <div class="card-info">
                <div class="icon-square">
                    <i class="bi bi-people-fill"></i>
                </div>

                <div>
                    <h6>Times</h6>
                    <h2>0</h2>
                </div>
            </div>
        </div>

<!-- QTD DE ALUNOS-->
        <div class="col-lg-3 col-md-6">
            <div class="card-info">
                <div class="icon-square">
                    <i class="bi bi-person-fill"></i>
                </div>

                <div>
                    <h6>Alunos</h6>
                    <h2>0</h2>
                </div>
            </div>
        </div>

    </div>
    <!-- Próximas Partidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-box">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>PRÓXIMAS PARTIDAS</h5>
                    <a href="#" class="link-custom">
                        Ver mais
                    </a>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="partida-item">
                            <small>MODALIDADE • Período</small>
                            <h5>Escola <span>x</span> Escola</h5>
                            <div class="infos">
                                <span>
                                    <i class="bi bi-calendar"></i>
                                    --/--/----
                                </span>

                                <span>
                                    <i class="bi bi-clock"></i>
                                    00:00
                                </span>

                                <span>
                                    <i class="bi bi-geo-alt"></i>
                                    Local
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="partida-item">
                            <small>MODALIDADE • Período</small>
                            <h5>Escola <span>x</span> Escola</h5>
                            <div class="infos">
                                <span>
                                    <i class="bi bi-calendar"></i>
                                    --/--/----
                                </span>

                                <span>
                                    <i class="bi bi-clock"></i>
                                    00:00
                                </span>

                                <span>
                                    <i class="bi bi-geo-alt"></i>
                                    Local
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Jogos ao Vivo -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dashboard-box">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>JOGOS AO VIVO</h5>
                    <a href="#" class="link-custom">
                        Ver mais
                    </a>
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="live-game">
                            <span class="badge bg-laranja mb-3">
                                AO VIVO
                            </span>
                            <div class="placar">
                                <span>
                                    Escola
                                </span>
                                <h2> 0 x 0</h2>
                                <span>Escola</span>
                            </div>
                            <p>Período</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="live-game">
                            <span class="badge bg-laranja mb-3">
                                AO VIVO
                            </span>
                            <div class="placar">
                                <span>
                                    Escola
                                </span>
                                <h2> 0 x 0</h2>
                                <span>Escola</span>
                            </div>
                            <p>Período</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!--Js para o funcionamento da sidebar-->
<script src="../../js/script.js"></script>
</body>
</html>