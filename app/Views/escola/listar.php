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
                <th>Nome</th>
                <th>Categoria</th>
            </tr>
            <?php foreach ($escolas as $escola): ?>
                <tr>
                    <td><?= htmlspecialchars($escola['nome']) ?></td>
                    <td><?= htmlspecialchars($escola['categoria_administrativa']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
</body>
</html>