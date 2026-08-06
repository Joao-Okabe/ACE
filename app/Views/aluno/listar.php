<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alunos</title>
</head>
<body>
    <div>
        <h2>Alunos cadastrados</h2>
        <a href="/alunos/cadastrar" class="button secondary">Cadastrar aluno</a>
    </div>
    <table>
        <tr>
            <th>Código</th>
            <th>Foto</th>
            <th>Nome</th>
            <th>RA</th>
            <th>Escola</th>
            <th>Telefone</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($alunos as $aluno): ?>
            <tr>
                <td><?= htmlspecialchars($aluno['cd_aluno']) ?></td>
                <td>
                <td><img src="<?= htmlspecialchars($aluno['foto_perfil']) ?>" alt="Foto"></td>
                <td><?= htmlspecialchars($aluno['nome']) ?></td>

                <td><?= htmlspecialchars($aluno['ra'] ?? '') ?></td>
                <td><?= htmlspecialchars($aluno['cd_escola'] ?? '') ?></td>
                <td><?= htmlspecialchars($aluno['telefone'] ?? '') ?></td>
                <td>
                    <a href="/alunos/editar?id=<?= urlencode($aluno['cd_aluno']) ?>">Editar</a>
                    <a href="/alunos/remover?id=<?= urlencode($aluno['cd_aluno']) ?>">Remover</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>