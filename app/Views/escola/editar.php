<?php
$dados = $escola ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar escola</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="page">
        <section class="form-panel">
            <h1>Editar escola</h1>

            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/escolas/atualizar?id=<?= urlencode($escola['cd_escola']) ?>" method="post" class="form-grid" enctype="multipart/form-data">
                <div class="field full">
                    <label for="nome">Nome da escola</label>
                    <input type="text" id="nome" name="nome" value="<?= $valor('nome') ?>" required>
                </div>

                <div class="field">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" value="<?= $valor('telefone') ?>">
                </div>

                <div class="field">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep" value="<?= $valor('cep') ?>" maxlength="9" inputmode="numeric">
                </div>

                <div class="field">
                    <label for="numero">Número</label>
                    <input type="text" id="numero" name="numero" value="<?= $valor('numero') ?>">
                </div>

                <div class="field">
                    <label for="categoria_administrativa">Categoria</label>
                    <select id="categoria_administrativa" name="categoria_administrativa" required>
                        <option value="">Selecione</option>
                        <option value="PUBLICA" <?= $valor('categoria_administrativa') === 'PUBLICA' ? 'selected' : '' ?>>Pública</option>
                        <option value="PRIVADA" <?= $valor('categoria_administrativa') === 'PRIVADA' ? 'selected' : '' ?>>Privada</option>
                    </select>
                </div>

                <div class="field full">
                    <label>Logo</label>
                    <div class="perfil-aluno">
                        <label for="img_logo" class="foto-perfil" id="fotoLogoEscolaEditar">
                            <?php if (!empty($dados['img_logo'] ?? null)): ?>
                                <img src="<?= htmlspecialchars($dados['img_logo']) ?>" alt="Logo" style="max-width:160px;" class="img-thumbnail">
                            <?php else: ?>
                                <i class="bi bi-camera-fill"></i>
                            <?php endif; ?>
                        </label>
                        <div class="text-perfil">
                            <p class="form-label">Editar brasão/logo</p>
                        </div>
                    </div>
                    <input type="file" accept="image/*" name="img_logo" id="img_logo">
                </div>

                <div class="actions full">
                    <a href="/escolas/listar" class="button secondary">Cancelar</a>
                    <button type="submit">Salvar</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
