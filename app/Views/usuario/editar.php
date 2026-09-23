<?php
$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars((string) ($dados[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../../bootstrap-5.3.8-dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="../../bootstrap-icons-1.13.1/bootstrap-icons.css">
	<link rel="stylesheet" href="../../css/geral.css">
	<link rel="stylesheet" href="../../css/layout.css">

	<title>Configurações do usuário</title>
</head>
<body>
<app-header></app-header>
<main class="content">
	<div class="card form-card shadow-sm">
		<h1 class="form-title">Configurações da conta</h1>
		<p class="form-subtitle">Atualize seus dados de acesso e perfil.</p>

		<?php if (!empty($_GET['sucesso'])): ?>
			<div class="alert alert-success">Dados atualizados com sucesso.</div>
		<?php endif; ?>
		<?php if (!empty($erro)): ?>
			<div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
		<?php endif; ?>

		<form action="/usuarios/atualizar" method="post" enctype="multipart/form-data">
			<div class="mb-3">
				<label for="nm_usuario" class="form-label">Nome</label>
				<input type="text" id="nm_usuario" name="nm_usuario" class="form-control form-input" value="<?= $valor('nm_usuario') ?>" required>
			</div>
			<div class="mb-3">
				<label for="email" class="form-label">E-mail</label>
				<input type="email" id="email" name="email" class="form-control form-input" value="<?= $valor('email') ?>" required>
			</div>
			<div class="mb-3">
				<label for="senha" class="form-label">Nova senha <small>(opcional)</small></label>
				<input type="password" id="senha" name="senha" class="form-control form-input" minlength="6" autocomplete="new-password">
			</div>
			<div class="mb-4">
				<label for="foto_perfil" class="form-label">Foto de perfil</label>
				<input type="file" id="foto_perfil" name="foto_perfil" class="form-control form-input" accept="image/jpeg,image/png,image/webp">
			</div>
			<div class="d-flex justify-content-end gap-3">
				<a href="/dashboard" class="btn btn-secondary">Cancelar</a>
				<button type="submit" class="btn btn-laranja">Salvar alterações</button>
			</div>
		</form>
	</div>
</main>
<script>
window.usuarioLogado = {
	nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>,
	email: <?= json_encode($usuario['email'] ?? '—') ?>,
	foto: <?= json_encode(upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg')) ?>
};
</script>
<script src="../../js/layout.js"></script>

</body>
</html>
