<?php

class AuthContext
{
    public static function id(): int
    {
        return 7;
    }
}

class Permissoes
{
    public static function podeGerenciarTime(int $idTime): bool
    {
        return true;
    }
}

class Competicao
{
    public function buscar(int $id): array
    {
        return ['cd_competicao' => $id];
    }
}

class CompeticaoService
{
    public function exigirPermissao(array $competicao): void
    {
    }
}

class Partida
{
}

class PartidaServiceTestStatement extends PDOStatement
{
    public function __construct(private readonly mixed $value)
    {
    }

    public function execute(?array $params = null): bool
    {
        return true;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        return $this->value;
    }
}

class PartidaServiceTestPDO extends PDO
{
    public bool $transaction = false;
    public int $commits = 0;
    public int $rollbacks = 0;

    public function __construct()
    {
    }

    public function beginTransaction(): bool
    {
        $this->transaction = true;
        return true;
    }

    public function commit(): bool
    {
        $this->commits++;
        $this->transaction = false;
        return true;
    }

    public function rollBack(): bool
    {
        $this->rollbacks++;
        $this->transaction = false;
        return true;
    }

    public function inTransaction(): bool
    {
        return $this->transaction;
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $value = str_contains($query, 'SELECT status FROM competicao') ? 'ATIVA' : 1;
        return new PartidaServiceTestStatement($value);
    }
}

class PartidaServiceTestModel extends Partida
{
    public array $dadosCriados = [];
    public array $timesVinculados = [];
    public array $dadosInfo = [];
    public array $timesConfronto = [1, 2];
    public bool $partidaExistenteNoConfronto = false;
    public bool $timesInscritos = true;
    public bool $falharCriacaoInfo = false;
    public int $capturasCompeticao = 0;

    public function criar(array $dados): int
    {
        $this->dadosCriados = $dados;
        return 55;
    }

    public function criarInfo(int $idPartida, array $dados): void
    {
        if ($this->falharCriacaoInfo) {
            throw new RuntimeException('Falha simulada ao salvar informações.');
        }
        $this->dadosInfo = $dados;
    }

    public function vincularTimes(int $idPartida, array $idsTimes): void
    {
        $this->timesVinculados = $idsTimes;
    }

    public function buscarConfronto(int $idConfronto): ?array
    {
        return ['cd_competicao' => 12, 'status' => 'AGENDADO'];
    }

    public function buscarTimesDefinidosConfronto(int $idConfronto): array
    {
        return $this->timesConfronto;
    }

    public function confrontoTemPartida(int $idConfronto): bool
    {
        return $this->partidaExistenteNoConfronto;
    }

    public function validarTimesCompeticao(int $idCompeticao, array $idsTimes): bool
    {
        return $this->timesInscritos;
    }

    public function capturarParticipantes(int $idPartida, array $idsTimes): void
    {
    }

    public function capturarParticipantesCompeticao(int $idCompeticao, array $idsTimes): void
    {
        $this->capturasCompeticao++;
    }
}

require_once dirname(__DIR__) . '/app/Modules/Partida/Services/PartidaService.php';

function assertPartidaTest(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

function dadosPartida(array $adicionais = []): array
{
    return array_merge([
        'dt_partida' => '2026-11-20',
        'inicio_em' => '2026-11-20T18:00',
        'local_partida' => 'Ginásio ACE',
        'visibilidade' => 'PUBLICA',
    ], $adicionais);
}

function criarPartidaComTeste(array $dados, ?PartidaServiceTestModel $modelo = null): array
{
    $pdo = new PartidaServiceTestPDO();
    $modelo ??= new PartidaServiceTestModel();
    $service = new PartidaService($pdo, $modelo);

    return [$service->criar($dados), $pdo, $modelo];
}

function esperarInvalidArgument(callable $operacao, string $mensagem): void
{
    try {
        $operacao();
    } catch (InvalidArgumentException) {
        return;
    }

    throw new RuntimeException($mensagem);
}

[$id, $pdo, $modelo] = criarPartidaComTeste(dadosPartida([
    'cd_esporte' => 1,
    'cd_modalidade' => 2,
    'cd_times' => [1, 2],
]));
assertPartidaTest($id === 55, 'Partida avulsa deveria ser criada.');
assertPartidaTest($modelo->dadosCriados['cd_competicao'] === null, 'Partida avulsa não deve receber competição.');
assertPartidaTest($modelo->dadosCriados['cd_esporte'] === 1, 'Partida avulsa deve guardar o esporte.');
assertPartidaTest($modelo->timesVinculados === [1, 2], 'Partida avulsa deve vincular as duas equipes.');
assertPartidaTest($pdo->commits === 1 && $pdo->rollbacks === 0, 'Partida avulsa deve confirmar a transação.');

[$id, $pdo, $modelo] = criarPartidaComTeste(dadosPartida([
    'cd_competicao' => 12,
    'cd_times' => [3, 4],
]));
assertPartidaTest($modelo->dadosCriados['cd_competicao'] === 12, 'Partida de competição deve manter a competição.');
assertPartidaTest($modelo->dadosCriados['cd_confronto'] === null, 'Partida de competição pode não ter confronto.');
assertPartidaTest($modelo->capturasCompeticao === 1, 'Participantes da competição devem ser capturados.');
assertPartidaTest($pdo->commits === 1, 'Partida de competição deve confirmar a transação.');

[$id, $pdo, $modelo] = criarPartidaComTeste(dadosPartida([
    'cd_competicao' => 12,
    'cd_confronto' => 20,
    'cd_times' => [],
]));
assertPartidaTest($modelo->dadosCriados['cd_confronto'] === 20, 'Partida deve manter o confronto.');
assertPartidaTest($modelo->timesVinculados === [1, 2], 'Equipes devem vir dos participantes definidos do confronto.');
assertPartidaTest($pdo->commits === 1, 'Partida vinculada ao confronto deve confirmar a transação.');

$pdo = new PartidaServiceTestPDO();
$service = new PartidaService($pdo, new PartidaServiceTestModel());
esperarInvalidArgument(
    static fn () => $service->criar(dadosPartida(['cd_confronto' => 20, 'cd_times' => []])),
    'Confronto sem competição deve ser rejeitado.'
);
assertPartidaTest($pdo->rollbacks === 1, 'Confronto sem competição deve reverter a transação.');

$pdo = new PartidaServiceTestPDO();
$service = new PartidaService($pdo, new PartidaServiceTestModel());
esperarInvalidArgument(
    static fn () => $service->criar(dadosPartida([
        'cd_esporte' => 1,
        'cd_modalidade' => 2,
        'cd_times' => [1, 'invalido'],
    ])),
    'ID de equipe inválido deve ser rejeitado.'
);
assertPartidaTest($pdo->rollbacks === 0, 'Validação de equipe deve ocorrer antes da transação.');

$modelo = new PartidaServiceTestModel();
$modelo->timesConfronto = [1];
$pdo = new PartidaServiceTestPDO();
$service = new PartidaService($pdo, $modelo);
try {
    $service->criar(dadosPartida([
        'cd_competicao' => 12,
        'cd_confronto' => 20,
        'cd_times' => [],
    ]));
    throw new RuntimeException('Confronto com BYE não deve gerar partida.');
} catch (DomainException $e) {
    assertPartidaTest(
        str_contains($e->getMessage(), 'dois participantes'),
        'BYE deve retornar conflito por participantes insuficientes.'
    );
}
assertPartidaTest($pdo->rollbacks === 1, 'Confronto com BYE deve reverter a transação.');

$modelo = new PartidaServiceTestModel();
$modelo->falharCriacaoInfo = true;
$pdo = new PartidaServiceTestPDO();
$service = new PartidaService($pdo, $modelo);
try {
    $service->criar(dadosPartida([
        'cd_esporte' => 1,
        'cd_modalidade' => 2,
        'cd_times' => [1, 2],
    ]));
    throw new RuntimeException('Falha ao persistir informações deveria abortar a criação.');
} catch (RuntimeException $e) {
    assertPartidaTest($e->getMessage() === 'Falha simulada ao salvar informações.', 'Erro simulado inesperado.');
}
assertPartidaTest($pdo->rollbacks === 1 && !$pdo->transaction, 'Falha durante criação deve reverter a transação.');

echo "PartidaService::criar: OK\n";
