<?php
$escola = $escola ?? [];
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
    <link rel="stylesheet" href="../../css/acessibilidade.css">

    <title>Editar escola</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

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
                    <img src="<?= htmlspecialchars(upload_url($dados['img_logo'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Logo" style="max-width:160px;" class="img-thumbnail">
                    <?php else: ?>
                    <i class="bi bi-camera-fill"></i>
                    <?php endif; ?>
                </label>
                <input type="file" accept="image/*" name="img_logo" id="img_logo" hidden>
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
                        <option value="Escola Municipal" <?= $valor('categoria_administrativa') === 'Escola Municipal' ? 'selected' : '' ?>>Escola Municipal</option>
                        <option value="Escola Estadual" <?= $valor('categoria_administrativa') === 'Escola Estadual' ? 'selected' : '' ?>>Escola Estadual</option>
                        <option value="Privada" <?= $valor('categoria_administrativa') === 'Privada' ? 'selected' : '' ?>>Privada</option>
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
                    <a href="/escolas/listar" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-laranja">Salvar</button>
                </div>
            </form>
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
