<?php
$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
$escolas = $escolas ?? [];
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

    <title>Cadastro aluno</title>
</head>
<body>
    <!--Navbar-->
    <nav class="navbar">
            <button id="menu-btn">
                <i class="bi bi-layout-sidebar"></i>
            </button>

            <div class="logo">
                <img src="../../img/logo-ace-laranja.png" alt="logo ace">
            </div>

            <div class="perfil">
                <i class="bi bi-bell notificacao"></i>
                <img src="<?= htmlspecialchars(upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Perfil">

                <div class="usuario">
                    <?php if ($usuario !== null): ?>
                        <span class="session-info">
                            <?= htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <span class="session-info">
                            Perfil: <?= htmlspecialchars(implode(', ', $usuario['papeis'] ?? []), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php else: ?>
                        <p class="session-info">Sessão inválida.</p>
                    <?php endif; ?>
                </div>
            </div>
    </nav>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="menu">

            <a href="/dashboard" class="menu-item ">
                <i class="bi bi-house-door-fill"></i>
                <span>Painel</span>
            </a>

            <a href="/escolas/listar" class="menu-item">
                <i class="bi bi-bank2"></i>
                <span>Escolas</span>
            </a>

            <a href="/alunos/listar" class="menu-item active">
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

    <!--Conteúdo-->
    <div class="content">
        <div class="card form-card shadow-sm">
            <div class="cabecalho mb-5">
            <div>
                <h2 class="form-title">Cadastro de Aluno</h2>
                <p class="form-subtitle">
                Preencha os dados do aluno para realizar o cadastro.
                </p>
            </div>
            <!-- Foto de perfil -->
            <div class="perfil-aluno">
                <label for="img" class="foto-perfil" id="fotoPerfil">
                    <i class="bi bi-camera-fill"></i>
                </label>
                <div class="text-perfil">
                    <p class="form-label">Adicionar foto de perfil</p>
                </div>
                <input type="file" id="img" accept="image/*" hidden>
            </div>
        </div>

        <?php if (!empty($_GET['sucesso'])): ?>
            <p class="alert success">Aluno(a) cadastrado(a) com sucesso.</p>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form action="/alunos" method="post" class="form-grid" enctype="multipart/form-data">
            <div class="row">

        <!-- NOME -->
                <div class="col-md-6 mb-4">
                    <label for="nome" class="form-label"> Nome do(a) aluno(a) </label>
                    <input type="text" id="nome" name="nome" value="<?= $valor('nome') ?>" required class="form-control form-input" placeholder="Digite o nome completo">
                </div>

        <!-- RA -->
                <div class="col-md-6 mb-4">
                    <label for="ra" class="form-label">RA</label>
                    <input type="text" id="ra" name="ra" value="<?= $valor('ra') ?>" class="form-control form-input" placeholder="Digite o RA">
                </div>

        <!-- DT NASCIMENTO -->
                <div class="col-md-6 mb-4">
                    <label for="data_nascimento" class="form-label">Data de nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?= $valor('data_nascimento') ?>" class="form-control form-input"> 
                </div>

        <!-- SEXO -->
                <div class="col-md-6 mb-4">
                    <label for="sexo" class="form-label">Sexo</label>
                    <select id="sexo" name="sexo" class="form-select form-input">
                        <option value="">Selecione</option>
                        <option value="M" <?= $valor('sexo') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $valor('sexo') === 'F' ? 'selected' : '' ?>>Feminino</option>
                        <option value="O" <?= $valor('sexo') === 'O' ? 'selected' : '' ?>>Outro</option>
                    </select>
                </div>

        <!-- EMAIL -->
                <div class="col-md-6 mb-4">
                    <label for="email" class="form-label">E-mail de acesso</label>
                    <div class="form-input-group">
                    <input type="email" id="email" name="email" value="<?= $valor('email') ?>" required class="form-control form-input" placeholder="Digite o e-mail">
                    </div>
                </div>

        <!-- senha -->
                <div class="col-md-6 mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" id="senha" name="senha" required class="form-control form-input" placeholder="Digite uma senha">
                </div>

        <!-- Escola -->
                <div class="col-md-6 mb-4">
                    <label for="escola" class="form-label">Escola</label>
                    <select id="escola" name="escola" required class="form-select form-input">
                        <option value="">Selecione</option>
                        <?php foreach ($escolas as $escola): ?>
                            <option value="<?= htmlspecialchars($escola['cd_escola']) ?>" <?= ($valor('escola') == $escola['cd_escola']) ? 'selected' : '' ?>><?= htmlspecialchars($escola['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

        <!-- Telefone -->
                <div class="col-md-6 mb-4">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="telefone" value="<?= $valor('telefone') ?>" class="form-control form-input" placeholder="(00) 00000-0000">
                </div>

        <!-- CEP -->
                <div class="col-md-6 mb-4">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" id="cepN" name="cep" value="<?= $valor('cep') ?>" maxlength="9" inputmode="numeric" class="form-control form-input" placeholder="00000-000">
                </div>

        <!-- Confirmar -->        
            <div class="text-end mt-4">

                <a href="/alunos/listar" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-laranja">Cadastrar Aluno</button>

            </div>

        </form>
    </div>
</div>
<!--Js para o funcionamento da sidebar-->
<script src="../../js/script.js"></script>

</body>
</html>