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
    <link rel="stylesheet" href="../../css/acessibilidade.css">

    <title>Cadastro aluno</title>
    <link rel="icon" type="image/png" href="../../img/logo-ace-completa.png">
</head>
<body>
    
<!-- Navbar e Sidebar -->
<app-header></app-header>

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
            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="/alunos/listar" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-laranja">Cadastrar Aluno</button>
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