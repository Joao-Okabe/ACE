<?php
$dados = $aluno ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
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

    <title>Editar aluno</title>
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

            <a href="=/dashboard" class="menu-item">
                <i class="bi bi-house-door-fill"></i>
                <span>Painel</span>
            </a>

            <a href="/escolas/listar" class="menu-item">
                <i class="bi bi-bank2"></i>
                <span>Escolas</span>
            </a>

            <a href="/alunos/listar" class="menu-item" active>
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

    <main class="page">
        <section class="form-panel">
            <h1>Editar aluno</h1>

            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/alunos/atualizar?id=<?= urlencode($aluno['cd_aluno']) ?>" method="post" class="form-grid" enctype="multipart/form-data">
                <div class="field full">
                    <label for="nome">Nome do(a) aluno(a)</label>
                    <input type="text" id="nome" name="nome" value="<?= $valor('nome') ?>" required>
                </div>

                <div class="field">
                    <label for="ra">RA</label>
                    <input type="text" id="ra" name="ra" value="<?= $valor('ra') ?>">
                </div>

                <div class="field">
                    <label for="data_nascimento">Data de nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?= $valor('data_nascimento') ?>">
                </div>

                <div class="field">
                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo">
                        <option value="">Selecione</option>
                        <option value="M" <?= $valor('sexo') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $valor('sexo') === 'F' ? 'selected' : '' ?>>Feminino</option>
                        <option value="O" <?= $valor('sexo') === 'O' ? 'selected' : '' ?>>Outro</option>
                    </select>
                </div>

                <div class="field">
                    <label for="escola">Escola</label>
                    <select id="escola" name="cd_escola">
                        <option value="">Selecione</option>
                        <?php foreach ($escolas as $escola): ?>
                            <option value="<?= htmlspecialchars($escola['cd_escola']) ?>" <?= $escola['cd_escola'] == $valor('cd_escola') ? 'selected' : '' ?>><?= htmlspecialchars($escola['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field full">
                    <label>Foto de perfil</label>
                    <?php if (!empty($dados['foto_perfil'])): ?>
                        <div class="preview">
                            <img src="<?= htmlspecialchars($dados['foto_perfil']) ?>" alt="Foto atual de <?= htmlspecialchars($dados['nome']) ?>" width="100" height="100">
                        </div>
                    <?php endif; ?>
                    <input type="file" accept="image/*" name="foto_perfil">
                </div>

                <div class="actions full">
                    <button type="submit">Salvar</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
