<?php

require_once dirname(__DIR__) . '/app/Core/Model.php';
require_once dirname(__DIR__) . '/app/Modules/Confronto/Models/ConfrontoModel.php';

class ConfrontoTestePDO extends PDO
{
    public array $confrontos = [];
    public array $participantes = [];
    public array $origens = [];
    private int $proximaOrigem = 100;

    public function __construct()
    {
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new ConfrontoTesteStatement($this, $query);
    }

    public function criarOrigem(array $origem): int
    {
        $id = $this->proximaOrigem++;
        $this->origens[$id] = $origem;
        return $id;
    }
}

class ConfrontoTesteStatement extends PDOStatement
{
    private array $linhas = [];
    private mixed $valor = false;

    public function __construct(
        private readonly ConfrontoTestePDO $pdo,
        private readonly string $query
    ) {
    }

    public function execute(?array $params = null): bool
    {
        $params ??= [];
        $this->linhas = [];
        $this->valor = false;

        if (str_contains($this->query, 'cd_vencedor = :vencedor')) {
            $id = (int) $params[':confronto'];
            $this->pdo->confrontos[$id]['cd_vencedor'] = $params[':vencedor'];
            $this->pdo->confrontos[$id]['status'] = 'FINALIZADO';
        } elseif (str_contains($this->query, 'SELECT cp.cd_confronto, cp.posicao, opc.resultado_confronto')) {
            $idAnterior = (int) $params[':confronto'];
            foreach ($this->pdo->participantes as $participante) {
                $origem = $this->pdo->origens[$participante['origem']];
                if (
                    $origem['tipo_origem'] === 'CONFRONTO'
                    && $origem['cd_confronto'] === $idAnterior
                ) {
                    $this->linhas[] = [
                        'cd_confronto' => $participante['confronto'],
                        'posicao' => $participante['posicao'],
                        'resultado_confronto' => $origem['resultado_confronto'],
                    ];
                }
            }
        } elseif (str_contains($this->query, "VALUES ('BYE')")) {
            $this->valor = $this->pdo->criarOrigem(['tipo_origem' => 'BYE']);
        } elseif (str_contains($this->query, "VALUES ('TIME', :time)")) {
            $this->valor = $this->pdo->criarOrigem([
                'tipo_origem' => 'TIME',
                'cd_time' => (int) $params[':time'],
            ]);
        } elseif (str_contains($this->query, 'UPDATE confronto_participante')) {
            foreach ($this->pdo->participantes as &$participante) {
                if (
                    $participante['confronto'] === (int) $params[':confronto']
                    && $participante['posicao'] === (int) $params[':posicao']
                ) {
                    $participante['origem'] = (int) $params[':origem'];
                    $participante['status'] = $params[':status'];
                }
            }
            unset($participante);
        } elseif (str_contains($this->query, 'SET status = CASE')) {
            $id = (int) $params[':confronto_update'];
            $resolvidos = 0;
            foreach ($this->pdo->participantes as $participante) {
                if ($participante['confronto'] !== $id) {
                    continue;
                }
                $origem = $this->pdo->origens[$participante['origem']];
                if (
                    ($participante['status'] === 'DEFINIDO' && $origem['tipo_origem'] === 'TIME')
                    || ($participante['status'] === 'BYE' && $origem['tipo_origem'] === 'BYE')
                ) {
                    $resolvidos++;
                }
            }
            if ($this->pdo->confrontos[$id]['status'] !== 'FINALIZADO') {
                $this->pdo->confrontos[$id]['status'] = $resolvidos === 2
                    ? 'PENDENTE'
                    : 'AGUARDANDO';
            }
        } elseif (str_contains($this->query, 'cp.status AS status_participante')) {
            $etapa = (int) $params[':etapa'];
            foreach ($this->pdo->participantes as $participante) {
                $id = $participante['confronto'];
                $confronto = $this->pdo->confrontos[$id];
                $origem = $this->pdo->origens[$participante['origem']];
                if (
                    $confronto['etapa'] !== $etapa
                    || in_array($confronto['status'], ['FINALIZADO', 'CANCELADO'], true)
                ) {
                    continue;
                }
                $this->linhas[] = [
                    'cd_confronto' => $id,
                    'status_participante' => $participante['status'],
                    'tipo_origem' => $origem['tipo_origem'],
                    'cd_time' => $origem['cd_time'] ?? null,
                ];
            }
        } else {
            throw new RuntimeException('Unexpected SQL in confrontation test.');
        }

        return true;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return $this->linhas;
    }

    public function fetchColumn(int $column = 0): mixed
    {
        return $this->valor;
    }
}

function assertConfrontoTeste(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

$pdo = new ConfrontoTestePDO();
$pdo->origens = [
    1 => ['tipo_origem' => 'TIME', 'cd_time' => 10],
    2 => ['tipo_origem' => 'BYE'],
    3 => ['tipo_origem' => 'TIME', 'cd_time' => 20],
    4 => ['tipo_origem' => 'TIME', 'cd_time' => 30],
    5 => ['tipo_origem' => 'CONFRONTO', 'cd_confronto' => 1, 'resultado_confronto' => 'VENCEDOR'],
    6 => ['tipo_origem' => 'CONFRONTO', 'cd_confronto' => 1, 'resultado_confronto' => 'PERDEDOR'],
    7 => ['tipo_origem' => 'CONFRONTO', 'cd_confronto' => 2, 'resultado_confronto' => 'VENCEDOR'],
    8 => ['tipo_origem' => 'CONFRONTO', 'cd_confronto' => 2, 'resultado_confronto' => 'PERDEDOR'],
    9 => ['tipo_origem' => 'CONFRONTO', 'cd_confronto' => 4, 'resultado_confronto' => 'VENCEDOR'],
];
$pdo->confrontos = [
    1 => ['etapa' => 7, 'status' => 'PENDENTE'],
    2 => ['etapa' => 7, 'status' => 'PENDENTE'],
    3 => ['etapa' => 7, 'status' => 'AGUARDANDO'],
    4 => ['etapa' => 7, 'status' => 'AGUARDANDO'],
    5 => ['etapa' => 7, 'status' => 'AGUARDANDO'],
];
$pdo->participantes = [
    ['confronto' => 1, 'posicao' => 1, 'origem' => 1, 'status' => 'DEFINIDO'],
    ['confronto' => 1, 'posicao' => 2, 'origem' => 2, 'status' => 'BYE'],
    ['confronto' => 2, 'posicao' => 1, 'origem' => 3, 'status' => 'DEFINIDO'],
    ['confronto' => 2, 'posicao' => 2, 'origem' => 4, 'status' => 'DEFINIDO'],
    ['confronto' => 3, 'posicao' => 1, 'origem' => 5, 'status' => 'PENDENTE'],
    ['confronto' => 3, 'posicao' => 2, 'origem' => 7, 'status' => 'PENDENTE'],
    ['confronto' => 4, 'posicao' => 1, 'origem' => 6, 'status' => 'PENDENTE'],
    ['confronto' => 4, 'posicao' => 2, 'origem' => 8, 'status' => 'PENDENTE'],
    ['confronto' => 5, 'posicao' => 1, 'origem' => 9, 'status' => 'PENDENTE'],
    ['confronto' => 5, 'posicao' => 2, 'origem' => 2, 'status' => 'BYE'],
];

$reflexao = new ReflectionClass(Confronto::class);
$modelo = $reflexao->newInstanceWithoutConstructor();
$reflexao->getProperty('pdo')->setValue($modelo, $pdo);

$modelo->processarByesEtapa(7);
assertConfrontoTeste($pdo->confrontos[1]['cd_vencedor'] === 10, 'A BYE must advance the real team.');
assertConfrontoTeste($pdo->origens[$pdo->participantes[4]['origem']]['cd_time'] === 10, 'The BYE winner must feed the next round.');
assertConfrontoTeste($pdo->participantes[6]['status'] === 'BYE', 'A BYE must not produce a loser.');

$modelo->definirResultado(2, 20, 30);
$modelo->processarByesEtapa(7);
assertConfrontoTeste($pdo->confrontos[4]['cd_vencedor'] === 30, 'The loser of the winners match must advance past the empty BYE slot.');
assertConfrontoTeste($pdo->origens[$pdo->participantes[8]['origem']]['cd_time'] === 30, 'The losers bracket winner must feed the grand final.');

echo "Confrontation winner/loser and BYE propagation: OK\n";
