<?php
$dados = $escola ?? [];
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

    <title>Editar escola</title>
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
                    <p class="alert error">Sessão inválida.</p>
                <?php endif; ?>
            </div>
            </div>
    </nav>

<!--Sidebar-->
    <div class="sidebar">
        <div class="menu">

            <a href="/dashboard" class="menu-item">
                <i class="bi bi-house-door-fill"></i>
                <span>Painel</span>
            </a>

            <a href="/escolas/listar" class="menu-item active">
                <i class="bi bi-bank2"></i>
                <span>Escolas</span>
            </a>

            <a href="/alunos/listar" class="menu-item">
                <i class="bi bi-person-fill"></i>
                <span>Alunos</span>
            </a>

            <a href="..." class="menu-item ">
                <i class="bi bi-people-fill"></i>
                <span>Times</span>
            </a>

            <a href="..." class="menu-item ">
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

            <div class="mb-2">
            <div>
                <h2 class="form-title">Editar Escola</h2>
                <p class="form-subtitle">
                Altere os dados da escola.
                </p>
            </div>
        </div>
            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/escolas/atualizar?id=<?= urlencode($escola['cd_escola']) ?>" method="post" class="form-grid" enctype="multipart/form-data">
            <!-- Foto de perfil -->
            <div class="perfil-aluno mb-4">
                <label for="img_logo" class="foto-perfil" id="fotoLogoEscolaEditar">
                    <?php if (!empty($dados['img_logo'] ?? null)): ?>
                    <img src="<?= htmlspecialchars($dados['img_logo']) ?>" alt="Logo" style="max-width:160px;" class="img-thumbnail">
                    <?php else: ?>
                    <i class="bi bi-camera-fill"></i>
                    <?php endif; ?>
                </label>
                <input type="file" accept="image/*" name="img_logo" id="img" hidden>
                <div class="text-perfil">
                    <p class="form-label">Editar brasão/logo</p>
                </div>
            </div>    
            
            <div class="row">    
            <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome da escola</label>
                    <input type="text" id="nome" name="nome" value="<?= $valor('nome') ?>" required class="form-control form-input">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="categoria_administrativa" class="form-label">Categoria</label>
                    <select id="categoria_administrativa" name="categoria_administrativa" required class="form-select form-input">
                        <option value="">Selecione</option>
                        <option value="PUBLICA" <?= $valor('categoria_administrativa') === 'PUBLICA' ? 'selected' : '' ?>>Pública</option>
                        <option value="PRIVADA" <?= $valor('categoria_administrativa') === 'PRIVADA' ? 'selected' : '' ?>>Privada</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="telefone" value="<?= $valor('telefone') ?>" class="form-control form-input">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" id="cep" name="cep" value="<?= $valor('cep') ?>" maxlength="9" inputmode="numeric" class="form-control form-input">
                </div>

                <div class="field">
                    <label for="numero" class="form-label">Número</label>
                    <input type="text" id="numero" name="numero" value="<?= $valor('numero') ?>" class="form-control form-input">
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="/escolas/listar" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-laranja">Salvar</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
