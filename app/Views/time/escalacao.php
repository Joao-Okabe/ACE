<?php

$tecnico = $tecnico ?? [];
$time = $time ?? [];
$integrantes = $integrantes ?? [];

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
    <link rel="stylesheet" href="../../css/escalacao.css">
    <link rel="stylesheet" href="../../css/visualizar.css">

    <title>Definir Escalação</title>
</head>
<body>

    
<app-header></app-header>

<div class="content">

    <div class="header-visualizar ">
        <a href="/times/escalacao" class="btn-voltar"><i class="bi bi-arrow-left"></i></a>
        <h2 class="form-title">Escalação</h2>
    </div>
    
    <div class="escalacao-layout">
        <div class="card-escalacao">

            <div class="campo-futsal">

                <!-- Pivô -->
                <div class="jogador pivo">
                    <img src="/img/alunos/joao.jpg" alt="João Silva">
                    <span>Nome</span>
                    <small>PIVÔ</small>
                </div>

                <!-- Ala esquerda -->
                <div class="jogador ala-esquerda">
                    <img src="/img/alunos/carlos.jpg" alt="Carlos Souza">
                    <span>Nome</span>
                    <small>ALA</small>
                </div>

                <!-- Ala direita -->
                <div class="jogador ala-direita">
                    <img src="/img/alunos/pedro.jpg" alt="Pedro Lima">
                    <span>Nome</span>
                    <small>ALA</small>
                </div>

                <!-- Fixo -->
                <div class="jogador fixo">
                    <img src="/img/alunos/rafael.jpg" alt="Rafael Santos">
                    <span>Nome</span>
                    <small>FIXO</small>
                </div>

                <!-- Goleiro -->
                <div class="jogador goleiro">
                    <img src="/img/alunos/bruno.jpg" alt="Bruno Alves">
                    <span>Nome</span>
                    <small>GOL</small>
                </div>

            </div>
        </div>

        <!-- CARD DOS SELECTS -->
        <div class="card-posicoes">

            <div class="mb-2">
                <h3 class="form-title">Definir escalação</h3>
                <p class="form-subtitle">Selecione um jogador para cada posição.</p>
            </div>

                <!-- Goleiro -->
                <div class="mb-4">
                    <label for="goleiro" class="form-label">Goleiro</label>
                    <select class="form-select form-input">
                        <option value="">Selecione o jogador</option>
                    </select>
                </div>

                <!-- Fixo -->
                <div class="mb-4">
                    <label for="fixo" class="form-label">Fixo</label>
                    <select class="form-select form-input">
                        <option value="">Selecione o jogador</option>
                    </select>
                </div>

                <!-- Ala-esquerda -->
                <div class="mb-4">
                    <label for="ala-esquerda" class="form-label">Ala-esquerda</label>
                    <select class="form-select form-input">
                        <option value="">Selecione o jogador</option>
                    </select>
                </div>

                <!-- Ala-direita -->
                <div class="mb-4">
                    <label for="ala-direita" class="form-label">Ala-direita</label>
                    <select class="form-select form-input">
                        <option value="">Selecione o jogador</option>
                    </select>
                </div>

                <!-- Pivô -->
                <div class="mb-4">
                    <label for="pivo" class="form-label">Pivô</label>
                    <select class="form-select form-input">
                        <option value="">Selecione o jogador</option>
                    </select>
                </div>


            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="/times/escalacao" class="btn btn-secondary">Cancelar</a>
                <button type="button" class="btn btn-laranja">Confirmar escalação</button>
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