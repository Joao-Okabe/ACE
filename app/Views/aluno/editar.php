<?php
$aluno = $aluno ?? [];
$dados = array_merge($aluno, $dados ?? []);
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


    <title>Editar aluno</title>
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
                <h2 class="form-title">Editar Aluno</h2>
                <p class="form-subtitle">Atualize os dados do aluno.</p>
            </div>
        </div>

        <?php if (!empty($_GET['sucesso'])): ?>
            <div class="alert alert-success auth-success" role="alert" aria-live="polite">
                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                <span>Aluno editado com sucesso.</span>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger auth-error" role="alert" aria-live="assertive">
                <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="/alunos/atualizar?id=<?= urlencode($aluno['cd_aluno']) ?>" method="post" class="form-grid" enctype="multipart/form-data">
            <div class="form-section">
                <div class="dados-iniciais">

                <!-- FOTO -->
                <div class="perfil-aluno">
                    <label for="foto_perfil" class="foto-perfil" id="fotoPerfil">
                        <?php if (!empty($dados['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars(upload_url($dados['foto_perfil'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="Foto do aluno" class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-camera-fill"></i>
                        <?php endif; ?>
                    </label>
                    <div class="text-perfil">
                    <p class="form-label">Editar foto</p>
                </div> 
                </div>

                <div class="dados-primeiros">

                <div class="campo-inicial">
                    <label for="nome" class="form-label">Nome do(a) aluno(a)</label>
                    <input type="text" id="nome" name="nome" class="form-control form-input" value="<?= $valor('nome') ?>" required placeholder="Digite o nome completo">
                </div>

                <div class="campo-inicial campo-ra">
                    <label for="ra" class="form-label">RA</label>
                    <input type="text" id="ra" name="ra" class="form-control form-input" value="<?= $valor('ra') ?>" placeholder="Digite o RA">
                </div>
            </div>

        </div>
        </div>

        <div class="form-section">

            <div class="row">

                <div class="col-md-6 mb-4">
                    <label for="data_nascimento" class="form-label">Data de nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" class="form-control form-input" value="<?= $valor('data_nascimento') ?>">
                </div>

                <div class="col-md-6 mb-4">
                    <label for="sexo" class="form-label">Sexo</label>
                    <select id="sexo" name="sexo" class="form-select form-input">
                        <option value="">Selecione</option>
                        <option value="M" <?= $valor('sexo') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $valor('sexo') === 'F' ? 'selected' : '' ?>>Feminino</option>
                        <option value="O" <?= $valor('sexo') === 'O' ? 'selected' : '' ?>>Outro</option>
                    </select>
                </div>
            
            </div>
        </div>

        <div class="form-section">

            <div class="row">

                <div class="col-md-6 mb-4">
                    <label for="cd_escola" class="form-label">Escola</label>
                    <select id="cd_escola" name="cd_escola" class="form-select form-input" required>
                        <option value="">Selecione</option>
                        <?php foreach ($escolas as $escola): ?>
                            <option value="<?= htmlspecialchars($escola['cd_escola']) ?>" <?= $escola['cd_escola'] == $valor('cd_escola') ? 'selected' : '' ?>><?= htmlspecialchars($escola['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-4">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-control form-input" value="<?= $valor('telefone') ?>" placeholder="(00) 00000-0000">
                </div>

                <div class="col-md-6 mb-4">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" id="cep" name="cep" class="form-control form-input" value="<?= $valor('cep') ?>" maxlength="9" inputmode="numeric" placeholder="00000-000">
                </div>
            
            </div>
        </div>

                <div class="col-md-12 mb-4">
                    <label for="foto_perfil" class="form-label">Foto de perfil</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" class="form-control form-input" accept="image/*">
                    <?php if (!empty($dados['foto_perfil'])): ?>
                        <div class="mt-3">
                            <img src="<?= htmlspecialchars(upload_url($dados['foto_perfil'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil" class="img-thumbnail" style="max-width: 160px;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="/alunos/listar" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-laranja">Salvar alterações</button>
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
