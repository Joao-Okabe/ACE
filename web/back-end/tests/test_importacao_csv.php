<?php

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
Autoloader::register();

function assertImportacao(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

$arquivo = tempnam(sys_get_temp_dir(), 'ace-csv-');
if ($arquivo === false) {
    throw new RuntimeException('Não foi possível criar o CSV temporário.');
}

try {
    file_put_contents(
        $arquivo,
        "\xEF\xBB\xBFnome;email;ra;data de nascimento;sexo;telefone;cep\n"
        . "\"Ana, Maria\";ana@example.com;00123;17/04/2010;F;(13) 99999-1234;11740-000\n"
    );
    $linhas = ImportacaoCsvService::parseCsv($arquivo, 'alunos');

    assertImportacao(count($linhas) === 1, 'O CSV de alunos deve produzir uma linha.');
    assertImportacao($linhas[0]['linha'] === 2, 'A numeração deve corresponder à linha do arquivo.');
    assertImportacao($linhas[0]['dados']['nome'] === 'Ana, Maria', 'Campos entre aspas devem preservar delimitadores.');
    assertImportacao($linhas[0]['dados']['ra'] === '00123', 'O RA deve preservar zeros à esquerda.');
    assertImportacao($linhas[0]['erros'] === [], 'Uma linha válida não deve ter erros estruturais.');

    file_put_contents(
        $arquivo,
        "nome;categoria administrativa;telefone\n"
        . "Escola Exemplo;Municipal;(13) 3426-1234\n"
    );
    $escolas = ImportacaoCsvService::parseCsv($arquivo, 'escolas');
    assertImportacao(
        $escolas[0]['dados']['categoria_administrativa'] === 'Municipal',
        'O cabeçalho com espaço deve mapear a categoria administrativa.'
    );

    file_put_contents($arquivo, "nome,email\nAluno,aluno@example.com\n");
    try {
        ImportacaoCsvService::parseCsv($arquivo, 'alunos');
        throw new RuntimeException('Um CSV sem colunas obrigatórias deveria ser rejeitado.');
    } catch (InvalidArgumentException $e) {
        assertImportacao(
            str_contains($e->getMessage(), 'Colunas obrigatórias'),
            'A mensagem deve informar a ausência das colunas obrigatórias.'
        );
    }
} finally {
    unlink($arquivo);
}

echo "CSV import parsing: OK\n";
