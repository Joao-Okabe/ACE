<?php
$usuario = $usuario ?? null;
$qtAluno = $qtAluno ?? 0;
$qtTime = $qtTime ?? 0;
$qtCompeticao = $qtCompeticao ?? 0;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <style>html,body {background: #faf8f5;}</style>

    <!--Bootstrap css-->
    <link rel="stylesheet" href="../../bootstrap-5.3.8-dist/css/bootstrap.min.css">

    <!--Bootstrap icons-->
    <link rel="stylesheet" href="../../bootstrap-icons-1.13.1/bootstrap-icons.css">

    <!--CSS-->
    <link rel="stylesheet" href="../../css/geral.css">
    <link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/painel.css">


    <title>Painel</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!-- Conteúdo -->
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="form-title">Bem-vindo, 
                <?= htmlspecialchars($usuario['nm_usuario'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?>!
            </h1>
            <p class="form-subtitle">
                Confira o resumo das competições.
            </p>
        </div>

        <div class="text-end">
            <span class="dashboard-date" id="dashboardDate">
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
                    <h2><?= htmlspecialchars((string) $qtTime, ENT_QUOTES, 'UTF-8') ?></h2>
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
                    <h2><?= htmlspecialchars((string) $qtAluno, ENT_QUOTES, 'UTF-8') ?></h2>
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

    <!--Atualização da data automático-->
    <script>
    function atualizarData() {
        const agora = new Date();
        const opcoes = {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        };
        const data = agora.toLocaleDateString('pt-BR', opcoes);
        const hora = agora.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit'
        });

        document.getElementById('dashboardDate').textContent =
            `${data} • ${hora}`;
        }

        atualizarData();
        setInterval(atualizarData, 1000);
    </script>

    <script>
        window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
        email: <?= json_encode($usuario['email'] ?? '—') ?>, 
        foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };
    </script>

    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>

</body>
</html>