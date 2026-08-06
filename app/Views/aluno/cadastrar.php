<?php
$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro de aluno</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="page">
        <section class="form-panel">
            <h1>Cadastro de aluno</h1>

            <?php if (!empty($_GET['sucesso'])): ?>
                <p class="alert success">Aluno(a) cadastrado(a) com sucesso.</p>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/alunos" method="post" class="form-grid" enctype="multipart/form-data">
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
                    <label for="email">E-mail de acesso</label>
                    <input type="email" id="email" name="email" value="<?= $valor('email') ?>" required>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <div class="field">
                    <label for="escola">Escola</label>
                    <select id="escola" name="escola" required>
                        <option value="">Selecione</option>
                        <?php foreach ($escolas as $escola): ?>
                            <option value="<?= htmlspecialchars($escola['cd_escola']) ?>" <?= ($valor('escola') == $escola['cd_escola']) ? 'selected' : '' ?>><?= htmlspecialchars($escola['nome']) ?></option>
                        <?php endforeach; ?>
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

                <div class="actions full">
                    <a href="/usuarios/cadastrar" class="button secondary">Cadastrar usuário</a>
                    <button type="submit">Cadastrar Aluno</button>
                </div>
            </form>
        </section>
    </main>
    <script src="/js/cep.api.js"></script>
</body>
</html>
