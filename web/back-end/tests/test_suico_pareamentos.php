<?php

class Competicao
{
}

class EtapaCompeticao
{
}

class RodadaCompeticao
{
}

class Confronto
{
}

class ConfrontoParticipante
{
}

class OrigemParticipanteConfronto
{
}

class Grupo
{
}

require_once dirname(__DIR__) . '/app/Modules/Chaveamento/Services/SuicoService.php';

function assertSuico(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

function validarRodadaSuica(
    array $pares,
    array $idsTimes,
    array $confrontosAnteriores,
    array $byesAnteriores
): ?int {
    $vistos = [];
    $byeDaRodada = null;

    foreach ($pares as [$timeA, $timeB]) {
        if ($timeB === null) {
            assertSuico($byeDaRodada === null, 'A round must have at most one bye.');
            assertSuico(!in_array($timeA, $byesAnteriores, true), 'A team must not receive a second bye.');
            $byeDaRodada = $timeA;
            continue;
        }

        assertSuico($timeA !== $timeB, 'A team cannot play itself.');
        $par = [$timeA, $timeB];
        sort($par);
        $chave = implode(':', $par);
        assertSuico(!isset($vistos[$chave]), 'A pairing was repeated in one round.');
        assertSuico(!isset($confrontosAnteriores[$chave]), 'A rematch was generated.');
        $vistos[$chave] = true;
    }

    $timesPareados = [];
    foreach ($pares as [$timeA, $timeB]) {
        $timesPareados[] = $timeA;
        if ($timeB !== null) {
            $timesPareados[] = $timeB;
        }
    }
    sort($timesPareados);
    $timesEsperados = $idsTimes;
    sort($timesEsperados);
    assertSuico($timesPareados === $timesEsperados, 'Every team must appear exactly once each round.');

    return $byeDaRodada;
}

$reflexao = new ReflectionClass(SuicoService::class);
$service = $reflexao->newInstanceWithoutConstructor();

$primeiraRodada = $reflexao->getMethod('emparelharPrimeiraRodada');
$primeiraRodada->setAccessible(true);
$emparelhar = $reflexao->getMethod('emparelharPorClassificacao');
$emparelhar->setAccessible(true);

$idsTimes = [1, 2, 3, 4, 5];
$paresR1 = $primeiraRodada->invoke($service, $idsTimes);
$byeR1 = validarRodadaSuica($paresR1, $idsTimes, [], []);

$pontos = array_fill_keys($idsTimes, 0.0);
$vitorias = array_fill_keys($idsTimes, 0);
$oponentes = array_fill_keys($idsTimes, []);
$byes = array_fill_keys($idsTimes, 0);
foreach ($paresR1 as [$timeA, $timeB]) {
    if ($timeB === null) {
        $pontos[$timeA] = 1.0;
        $byes[$timeA]++;
        continue;
    }

    $vencedor = min($timeA, $timeB);
    $perdedor = max($timeA, $timeB);
    $pontos[$vencedor] = 1.0;
    $vitorias[$vencedor]++;
    $oponentes[$timeA][] = $timeB;
    $oponentes[$timeB][] = $timeA;
}

$buchholz = array_fill_keys($idsTimes, 0.0);
foreach ($idsTimes as $idTime) {
    foreach ($oponentes[$idTime] as $idOponente) {
        $buchholz[$idTime] += $pontos[$idOponente];
    }
}
$standings = array_map(
    static fn (int $idTime): array => [
        'id' => $idTime,
        'pontos' => $pontos[$idTime],
        'vitorias' => $vitorias[$idTime],
        'byes' => $byes[$idTime],
        'oponentes' => $oponentes[$idTime],
        'buchholz' => $buchholz[$idTime],
    ],
    $idsTimes
);
usort(
    $standings,
    static fn (array $a, array $b): int =>
        ($b['pontos'] <=> $a['pontos'])
        ?: ($b['buchholz'] <=> $a['buchholz'])
        ?: ($b['vitorias'] <=> $a['vitorias'])
        ?: ($a['id'] <=> $b['id'])
);

$historico = [];
foreach ($oponentes as $idTime => $adversarios) {
    foreach ($adversarios as $idOponente) {
        $par = [(int) $idTime, $idOponente];
        sort($par);
        $historico[implode(':', $par)] = true;
    }
}

$paresR2 = $emparelhar->invoke($service, $standings);
$byeR2 = validarRodadaSuica($paresR2, $idsTimes, $historico, array_filter(
    array_keys($byes),
    static fn (int $idTime): bool => $byes[$idTime] > 0
));

assertSuico($byeR1 !== $byeR2, 'The same team must not receive consecutive byes.');
echo "Swiss pairings and bye allocation: OK\n";
