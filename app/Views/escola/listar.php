<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACE</title>
</head>
<body>
    <h2>Escolas cadastradas</h2>
        <table>
            <tr>
                <th>Código da Escola</th>
                <th>Nome</th>
                <th>Telefone</th>
                <th>CEP</th>
                <th>Estado</th>
                <th>Categoria</th>
            </tr>
            <?php foreach ($escolas as $escola): ?>
                <tr>
                    <td><?= htmlspecialchars($escola['cd_escola']) ?></td>
                    <td><?= htmlspecialchars($escola['nome']) ?></td>
                    <td><?= htmlspecialchars($escola['telefone']) ?></td>
                    <td><?= htmlspecialchars($escola['cep']) ?></td>
                    <td><? if (($escola['ativa']) == 1 ) { echo "Ativa"; } else { echo "Inativa"; } ?></td>
                    <td><?= htmlspecialchars($escola['categoria_administrativa']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
</body>
</html>