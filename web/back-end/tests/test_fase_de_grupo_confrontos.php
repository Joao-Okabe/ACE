<?php

class Competicao
{
}

class EtapaCompeticao
{
}

class RodadaCompeticao
{
    public array $criadas = [];

    public function criar(array $dados): int
    {
        $id = 100 + count($this->criadas);
        $this->criadas[$dados['nr_rodada']] = $id;
        return $id;
    }
}

class Confronto
{
    public array $criados = [];

    public function criar(array $dados): int
    {
        $id = count($this->criados) + 1;
        $this->criados[$id] = $dados;
        return $id;
    }
}

class ConfrontoParticipante
{
    public array $criados = [];

    public function criar(array $dados): void
    {
        $this->criados[] = $dados;
    }
}

class OrigemParticipanteConfronto
{
    public function criarTime(int $idTime): int
    {
        return $idTime;
    }
}

class Grupo
{
}

require_once dirname(__DIR__) . '/app/Modules/Chaveamento/Services/FaseDeGrupoService.php';

function assertFaseDeGrupo(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

$rodadas = new RodadaCompeticao();
$confrontos = new Confronto();
$participantes = new ConfrontoParticipante();
$origens = new OrigemParticipanteConfronto();
$reflexao = new ReflectionClass(FaseDeGrupoService::class);
$service = $reflexao->newInstanceWithoutConstructor();

foreach ([
    'rodadaCompeticaoModel' => $rodadas,
    'confrontoModel' => $confrontos,
    'confrontoParticipanteModel' => $participantes,
    'origemParticipanteConfrontoModel' => $origens,
] as $propriedade => $valor) {
    $reflexao->getProperty($propriedade)->setValue($service, $valor);
}

$metodo = $reflexao->getMethod('gerarConfrontosGrupos');
$metodo->setAccessible(true);
$metodo->invoke($service, 1, [
    array_map(static fn (int $id): array => ['cd_time' => $id], [1, 2, 3, 4]),
    array_map(static fn (int $id): array => ['cd_time' => $id], [5, 6, 7]),
    [['cd_time' => 8]],
]);

assertFaseDeGrupo(count($rodadas->criadas) === 3, 'Expected one shared row per round.');
assertFaseDeGrupo(count($confrontos->criados) === 9, 'Expected every round-robin pairing.');
assertFaseDeGrupo(count($participantes->criados) === 18, 'Each confrontation must have two participants.');

$numerosPorRodada = [];
$paresCriados = [];
foreach ($confrontos->criados as $idConfronto => $confronto) {
    $participantesDoConfronto = array_values(array_filter(
        $participantes->criados,
        static fn (array $participante): bool =>
            $participante['cd_confronto'] === $idConfronto
    ));
    $idsTimes = array_column($participantesDoConfronto, 'cd_origem_participante');
    sort($idsTimes);
    $chavePar = implode(':', $idsTimes);
    $idRodada = $confronto['cd_rodada'];

    assertFaseDeGrupo(count($idsTimes) === 2, 'Each confrontation must contain two teams.');
    assertFaseDeGrupo(
        !isset($paresCriados[$chavePar]),
        'A team pairing must not be repeated.'
    );

    $paresCriados[$chavePar] = true;
    $numerosPorRodada[$idRodada][] = $confronto['nr_confronto'];
}

$paresEsperados = [];
foreach ([[1, 2, 3, 4], [5, 6, 7]] as $timesDoGrupo) {
    for ($i = 0; $i < count($timesDoGrupo); $i++) {
        for ($j = $i + 1; $j < count($timesDoGrupo); $j++) {
            $paresEsperados[] = $timesDoGrupo[$i] . ':' . $timesDoGrupo[$j];
        }
    }
}
$paresGerados = array_keys($paresCriados);
sort($paresEsperados);
sort($paresGerados);
assertFaseDeGrupo($paresGerados === $paresEsperados, 'Expected exactly one match for every group pairing.');

foreach ($numerosPorRodada as $numeros) {
    sort($numeros);
    assertFaseDeGrupo($numeros === [1, 2, 3], 'Confrontation numbers must be continuous across groups.');
}

echo "Group-stage round-robin checks passed.\n";
