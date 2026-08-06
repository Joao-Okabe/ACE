<?php
$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro de escola</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="page">
        <section class="form-panel">
            <h1>Cadastro de escola</h1>

            <?php if (!empty($_GET['sucesso'])): ?>
                <p class="alert success">Escola cadastrada com sucesso.</p>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/escolas" method="post" class="form-grid" enctype="multipart/form-data">
                <div class="field full">
                    <label for="nome">Nome da escola</label>
                    <input type="text" id="nome" name="nome" value="<?= $valor('nome') ?>" required>
                </div>

                <div class="field">
                    <label for="email">E-mail de acesso</label>
                    <input type="email" id="email" name="email" value="<?= $valor('email') ?>" required>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <div class="field">
                    <label for="categoria_administrativa">Categoria</label>
                    <select id="categoria_administrativa" name="categoria_administrativa" required>
                        <option value="">Selecione</option>
                        <option value="PUBLICA" <?= $valor('categoria_administrativa') === 'PUBLICA' ? 'selected' : '' ?>>Pública</option>
                        <option value="PRIVADA" <?= $valor('categoria_administrativa') === 'PRIVADA' ? 'selected' : '' ?>>Privada</option>
                    </select>
                </div>

                <div class="field">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" value="<?= $valor('telefone') ?>">
                </div>

                <div class="field">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep" value="<?= $valor('cep') ?>" maxlength="9" inputmode="numeric">
                </div>

                <div class="field full">
                    <label for="logradouro">Logradouro</label>
                    <input type="text" id="logradouro" name="logradouro" value="<?= $valor('logradouro') ?>">
                </div>

                <div class="field">
                    <label for="numero">Número</label>
                    <input type="text" id="numero" name="numero" value="<?= $valor('numero') ?>">
                </div>

                <div class="field">
                    <label for="bairro">Bairro</label>
                    <input type="text" id="bairro" name="bairro" value="<?= $valor('bairro') ?>">
                </div>

                <div class="field">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" value="<?= $valor('cidade') ?>">
                </div>

                <div class="field">
                    <label for="uf">UF</label>
                    <input type="text" id="uf" name="uf" value="<?= $valor('uf') ?>" maxlength="2">
                </div>

                <div class="field full">
                    <label>Selecione a imagem:</label>
                    <div class="perfil-aluno">
                        <label for="img_logo" class="foto-perfil" id="fotoLogoEscola">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                        <div class="text-perfil">
                            <p class="form-label">Adicionar brasão/logo</p>
                        </div>
                    </div>
                    <input type="file" accept="image/*" name="img_logo" id="img_logo">
                </div>
                <div class="actions full">
                    <a href="/usuarios/cadastrar" class="button secondary">Cadastrar usuário</a>
                    <button type="submit">Cadastrar escola</button>
                </div>
            </form>
        </section>
    </main>
    <script src="/js/cep.api.js"></script>
</body>
</html>
