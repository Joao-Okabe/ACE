<?php
$dados = $aluno ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar aluno</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
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
