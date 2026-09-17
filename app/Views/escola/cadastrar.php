<?php
$dados = $dados ?? [];
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

    <title>Cadastro escola</title>
</head>
<body>

<!-- Navbar e Sidebar -->
<app-header></app-header>

<!--Conteúdo-->
<div class="content">
     <div class="card form-card shadow-sm">
        <div class="mb-2">
            <div>
                <h2 class="form-title">Cadastro da Escola</h2>
                <p class="form-subtitle">
                Preencha os dados da escola para realizar o cadastro.
                </p>
            </div>
        </div>

            <?php if (!empty($_GET['sucesso'])): ?>
                <p class="alert success">Escola cadastrada com sucesso.</p>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

        <form action="/escolas" method="post" class="form-grid" enctype="multipart/form-data">

            <!-- Foto de perfil -->
            <div class="perfil-aluno mb-4">
                <label for="img_logo" class="foto-perfil" id="fotoPerfil">
                    <i class="bi bi-camera-fill"></i>
                </label>
                <div class="text-perfil">
                    <p class="form-label">Adicionar imagem do brasão</p>
                </div>
                <input type="file" accept="image/*" name="img_logo" id="img_logo" hidden>
            </div>

            <div class="row">      
            <div class="col-md-6 mb-3">
                <label class="form-label">Nome da escola</label>
                <input type="text" class="form-control form-input" type="text" id="nome" name="nome" value="<?= $valor('nome') ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="categoria_administrativa">Categoria</label>
                <select class="form-select form-input" id="categoria_administrativa" name="categoria_administrativa" required>
                    <option selected>Selecione</option>
                    <option value="">Selecione</option>
                    <option value="Escola Municipal" <?= $valor('categoria_administrativa') === 'Escola Municipal' ? 'selected' : '' ?>>Escola Municipal</option>
                    <option value="Escola Estadual" <?= $valor('categoria_administrativa') === 'Escola Estadual' ? 'selected' : '' ?>>Escola Estadual</option>
                    <option value="Privada" <?= $valor('categoria_administrativa') === 'Privada' ? 'selected' : '' ?>>Privada</option>
                </select>
            </div> 

            <div class="col-md-6 mb-3">
                <label class="form-label" for="email">E-mail da Escola</label>
                <input type="email" class="form-control form-input" id="email" name="email" value="<?= $valor('email') ?>" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="senha">Senha</label>
                <input type="password" class="form-control form-input" type="password" id="senha" name="senha" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="telefone">Telefone</label>
                <input type="tel" class="form-control form-input" type="text" id="telefone" name="telefone" value="<?= $valor('telefone') ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="cep">CEP</label>
                <input type="text" class="form-control form-input" maxlength="9" type="text" id="cep" name="cep" value="<?= $valor('cep') ?>" inputmode="numeric">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="logradouro">Logradouro</label>
                <input type="text" class="form-control form-input" type="text" id="logradouro" name="logradouro" value="<?= $valor('logradouro') ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="numero">Número</label>
                <input type="text" id="numero" name="numero" value="<?= $valor('numero') ?>" class="form-control form-input">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="bairro">Bairro</label>
                <input type="text" class="form-control form-input" type="text" id="bairro" name="bairro" value="<?= $valor('bairro') ?>">
            </div>
            
            <div class="col-md-6 mb-3">
                <label class="form-label">Cidade</label>
                <input type="text" id="cidade" name="cidade" value="<?= $valor('cidade') ?>" class="form-control form-input">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="uf">UF</label>
                <input type="text" id="uf" name="uf" value="<?= $valor('uf') ?>" maxlength="2" class="form-control form-input" maxlength="2">
            </div>

            <div class="actions full d-flex justify-content-end gap-3 mt-4">
                <a href="/escolas/listar" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-laranja" type="submit">Cadastrar escola</button>
            </div>
        </form>
    </div>
</div>

    <script>
        window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
        email: <?= json_encode($usuario['email'] ?? '—') ?>, 
        foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };
    </script>
    
    <script src="/js/cep.api.js"></script>
    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>
    <script src="../../js/acessibilidade.js"></script>

</body>
</html>
