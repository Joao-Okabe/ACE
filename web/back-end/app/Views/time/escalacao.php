<?php

$time = $time ?? [];
$integrantes = $integrantes ?? [];
$posicoes = [
    'pivo' => ['nome' => 'Pivô', 'funcoes' => ['pivô', 'pivo']],
    'ala-esquerda' => ['nome' => 'Ala-esquerda', 'funcoes' => ['alas esquerdo', 'ala esquerda', 'ala esquerdo']],
    'ala-direita' => ['nome' => 'Ala-direita', 'funcoes' => ['alas direito', 'ala direita', 'ala direito']],
    'fixo' => ['nome' => 'Fixo', 'funcoes' => ['fixo']],
    'goleiro' => ['nome' => 'Goleiro', 'funcoes' => ['goleiro']],
];

foreach ($posicoes as &$posicao) {
    $posicao['integrantes'] = array_values(array_filter($integrantes, static function (array $integrante) use ($posicao): bool {
        return in_array(strtolower(trim($integrante['nm_funcao_integrante'] ?? '')), $posicao['funcoes'], true);
    }));
}
unset($posicao);

$podeGerenciar = $podeGerenciar ?? false;
$titularesSalvos = array_filter($integrantes, static fn (array $i): bool => in_array($i['titular'] ?? null, [true, 1, '1', 't'], true));
$titularesSalvos = array_map(static fn (array $i): int => (int) $i['cd_usuario'], $titularesSalvos);
?>
<!DOCTYPE html>
<html lang="pt-br">
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
    <link rel="stylesheet" href="../../css/escalacao.css">
    <link rel="stylesheet" href="../../css/visualizar.css">

    <title>Definir Escalação</title>
    <link rel="icon" type="image/png" href="../../img/icon.png">
</head>
<body>


<app-header></app-header>

<div class="content">

    <div class="header-visualizar ">
        <a href="/times/visualizar?id=<?= (int) $time['cd_time'] ?>" class="btn-voltar" aria-label="Voltar ao time"><i class="bi bi-arrow-left"></i></a>
        <h2 class="form-title">Escalação</h2>
    </div>

    <div class="escalacao-layout">
        <div class="card-escalacao">

            <div class="campo-futsal">

                <?php foreach ($posicoes as $chave => $posicao): ?>
                    <?php $jogador = $posicao['integrantes'][0] ?? null; ?>
                    <div class="jogador <?= $chave ?>" data-posicao="<?= $chave ?>">
                        <img src="<?= htmlspecialchars(upload_url($jogador['foto_perfil'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($jogador['nm_usuario'] ?? 'jogador', ENT_QUOTES, 'UTF-8') ?>" <?= $jogador ? '' : 'hidden' ?>>
                        <span><?= htmlspecialchars($jogador['nm_usuario'] ?? 'Sem integrante', ENT_QUOTES, 'UTF-8') ?></span>
                        <small><?= htmlspecialchars($posicao['nome'], ENT_QUOTES, 'UTF-8') ?></small>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- CARD DOS SELECTS -->
        <div class="card-posicoes">

            <?php if (!empty($sucesso)): ?>
                <div class="alert alert-success">Escalacão salva com sucesso.</div>
            <?php elseif (!empty($erro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="mb-2">
                <h3 class="form-title">Integrantes por função</h3>
                <p class="form-subtitle"><?= $podeGerenciar ? 'Selecione os jogadores titulares e salve a escalacão.' : 'Apenas o responsável pelo time pode alterar a escalacão.' ?></p>
            </div>

            <form method="post" action="/times/escalacao">
                <input type="hidden" name="id_time" value="<?= (int) $time['cd_time'] ?>">

                <?php foreach (['goleiro', 'fixo', 'ala-esquerda', 'ala-direita', 'pivo'] as $chave): ?>
                    <?php $posicao = $posicoes[$chave]; ?>
                    <div class="mb-4">
                        <label for="<?= $chave ?>" class="form-label"><?= htmlspecialchars($posicao['nome'], ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="<?= $chave ?>" name="titulares[<?= $chave ?>]" class="form-select form-input" data-posicao="<?= $chave ?>" <?= $podeGerenciar ? '' : 'disabled' ?>>
                            <option value="">Nenhum jogador nesta posição</option>
                            <?php foreach ($posicao['integrantes'] as $integrante): ?>
                                <option value="<?= (int) $integrante['cd_usuario'] ?>" data-foto="<?= htmlspecialchars(upload_url($integrante['foto_perfil'] ?? '/img/perfil.jpg'), ENT_QUOTES, 'UTF-8') ?>" <?= in_array((int) $integrante['cd_usuario'], $titularesSalvos, true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($integrante['nm_usuario'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>

                <?php if ($podeGerenciar): ?>
                    <button type="submit" class="btn btn-laranja">Salvar escalacão</button>
                <?php endif; ?>

                <a href="/times/visualizar?id=<?= (int) $time['cd_time'] ?>" class="btn btn-secondary">Voltar ao time</a>
            </form>

    </div>

</div>
</div>




    <script>
        window.usuarioLogado = { nome: <?= json_encode($usuario['nome'] ?? 'Usuário') ?>,
        email: <?= json_encode($usuario['email'] ?? '—') ?>,
        foto: <?= json_encode( upload_url($usuario['foto_perfil'] ?? '/img/perfil.jpg') ) ?> };
    </script>

    <script>
        document.querySelectorAll('select[data-posicao]').forEach(select => {
            select.addEventListener('change', () => {
                const jogador = document.querySelector(`.jogador[data-posicao="${select.dataset.posicao}"]`);
                const foto = jogador.querySelector('img');
                const selecionado = select.selectedOptions[0];
                jogador.querySelector('span').textContent = selecionado.value ? selecionado.textContent : 'Sem integrante';
                foto.hidden = !selecionado.value;
                if (selecionado.value) {
                    foto.src = selecionado.dataset.foto;
                    foto.alt = `Foto de ${selecionado.textContent}`;
                }
            });
        });
    </script>
    <script src="../../js/script.js"></script>
    <script src="../../js/layout.js"></script>

</body>
</html>