<?php
$vinculos = $vinculos ?? [];

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
    <link rel="stylesheet" href="../../css/visualizar.css">
    <link rel="stylesheet" href="../../css/chaveamento.css">

	<title>Configurações do usuário</title>
	<link rel="icon" type="image/png" href="../../img/icon.png">
</head>
<body>
<app-header></app-header>
<div class="content">

	<div class="visu-box">
				<div class="competicao-titulo">
					<a href="/dashboard" class="btn-voltar"><i class="bi bi-arrow-left"></i></a>
					<div class="competicao-info">
						<h2 class="name-competicao">Configurações da conta</h2>
					</div>
				</div>
				

		<!-- Botões -->
		<div class="competicao-tabs" role="tablist">

			<button type="button" class="competicao-tab active" data-tab="info_conta">
				<i class="bi bi-file-earmark-medical"></i>
				Informações da Conta
			</button>

			<button type="button" class="competicao-tab" data-tab="vinculo_conta">
				<i class="bi bi-link"></i>
				Vinculos da Conta
			</button>

			<button type="button" class="competicao-tab" data-tab="convites">
				<i class="bi bi-envelope"></i>
				Convites 
			</button>

			<button type="button" class="competicao-tab" data-tab="opt_acessibilidade">
				<i class="bi bi-file-earmark-richtext-fill"></i>
				Opções de acessibilidade
			</button>
		</div>


		<!-- Conteúdo -->
		<div class="competicao-conteudo">

			<!-- Informações da Conta -->
			<div class="competicao-painel active" id="info_conta">
				<div class="form-card shadow-sm">
					<h1 class="form-title">Alterar Dados</h1>
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
			</div>

			<!-- Vínculos da Conta -->
			<div class="competicao-painel" id="vinculo_conta">
				<div class="form-card shadow-sm mt-5">
					<h1 class="form-title">Vinculos da conta</h1>
					<p class="form-subtitle">Confira seus vinculos com escolas e seus respectivos papeis.</p>

					<div class="card-body">
						<?php if (empty($vinculos)): ?>
							<p class="text-muted mb-0">Nenhum vínculo encontrado.</p>
						<?php else: ?>
							<div class="table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th>Escola</th>
											<th>Papel</th>
											<th>Status</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($vinculos as $vinculo): ?>
											<tr>
												<td><?= htmlspecialchars($vinculo['nm_escola']) ?></td>
												<td><?= htmlspecialchars($vinculo['nm_papel']) ?></td>
												<td>
													<?php if ($vinculo['ativo']): ?>
														<span class="badge bg-success">Ativo</span>
													<?php else: ?>
														<span class="badge bg-secondary">Inativo</span>
													<?php endif; ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Convites -->
			<div class="competicao-painel" id="convites">
				<div class="form-card shadow-sm mt-5">
					<h1 class="form-title">Convites</h1>
					<p class="form-subtitle">Convites para Escolas, Times e etc aparecerão aqui.</p>
				</div>
			</div>

			<!-- Opções de Acessibilidade -->
			<div class="competicao-painel" id="opt_acessibilidade">
				<div class="form-card shadow-sm mt-5">
					<h1 class="form-title">Opções de Acessibilidade</h1>
					<p class="form-subtitle">Altere filtros de daltonismo e tamanho da fonte aqui.</p>
				</div>
			</div>

		</div>

	</div>
</div>
<script>

window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>, 
email: <?= json_encode($usuario['email'] ?? '—') ?>, 
foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };

document.querySelectorAll('.competicao-tab').forEach(botao => {

    botao.addEventListener('click', () => {

        const tab = botao.dataset.tab;

        // Remove ativo dos botões
        document.querySelectorAll('.competicao-tab').forEach(btn => {
            btn.classList.remove('active');
        });

        // Remove ativo dos conteúdos
        document.querySelectorAll('.competicao-painel').forEach(painel => {
            painel.classList.remove('active');
        });

        // Ativa botão clicado
        botao.classList.add('active');

        // Mostra conteúdo correspondente
        document.getElementById(tab).classList.add('active');
    });

});
</script>

<script src="../../js/script.js"></script>
<script src="../../js/layout.js"></script>

</body>
</html>