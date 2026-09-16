<?php
$usuario = $usuario ?? null;
$dados = $dados ?? [];
$valor = static fn (string $campo): string => htmlspecialchars($dados[$campo] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Competição</title>
</head>
<body>
    
<?php if (!empty($_GET['sucesso'])): ?>
    <p class="alert success">Competição cadastrada com sucesso.</p>
<?php endif; ?>

<?php if (!empty($erro)): ?>
    <p class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form action="/competicoes" method="POST" enctype="multipart/form-data">
    <label for="nm_competicao">Nome da competição</label>
    <input type="text" id="nm_competicao" value="<?= $valor('nm_competicao') ?>" name="nm_competicao" required>

    <label for="dt_inicio">Data de início</label>
    <input type="date" id="dt_inicio" value="<?= $valor('dt_inicio') ?>" name="dt_inicio">

    <label for="dt_encerramento">Data de encerramento</label>
    <input type="date" id="dt_encerramento" value="<?= $valor('dt_encerramento') ?>" name="dt_encerramento">

    <button type="submit">Cadastrar competição</button>
</form>

</body>
</html>