<?php

class RouteTestResponseHalt extends RuntimeException
{
    public function __construct(
        public readonly array $payload,
        public readonly int $status
    ) {
        parent::__construct();
    }
}

class Response
{
    public static array $payload = [];
    public static int $status = 200;
    public static bool $halt = false;

    public static function json(array $data, int $status = 200): void
    {
        self::$payload = $data;
        self::$status = $status;
        if (self::$halt) {
            throw new RouteTestResponseHalt($data, $status);
        }
    }

    public static function error(string $code, string $message, int $status): void
    {
        self::json(['error' => ['code' => $code, 'message' => $message]], $status);
    }

    public static function success(array|null $data = null, string $message = '', int $status = 200): void
    {
        self::json(['success' => true, 'data' => $data, 'message' => $message], $status);
    }
}

class JwtMiddleware
{
    public static function autenticar(): bool
    {
        return true;
    }
}

require_once dirname(__DIR__) . '/app/Core/Router.php';
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
Autoloader::register();

$router = new Router();
require dirname(__DIR__) . '/routes/api.php';

$rotasProperty = new ReflectionProperty(Router::class, 'routes');
$rotas = $rotasProperty->getValue($router);
$disparada = null;
$rotas['GET']['/api/v1/partidas/ao-vivo'] = static function () use (&$disparada): void {
    $disparada = 'ao-vivo';
};
$rotas['GET']['/api/v1/partidas/historico'] = static function () use (&$disparada): void {
    $disparada = 'historico';
};
$rotas['GET']['/api/partidas/ao-vivo'] = static function () use (&$disparada): void {
    $disparada = 'web-ao-vivo';
};
$rotas['GET']['/api/partidas/historico'] = static function () use (&$disparada): void {
    $disparada = 'web-historico';
};
$rotasProperty->setValue($router, $rotas);

foreach ([
    '/api/v1/partidas/ao-vivo' => 'ao-vivo',
    '/api/v1/partidas/historico' => 'historico',
    '/api/partidas/ao-vivo' => 'web-ao-vivo',
    '/api/partidas/historico' => 'web-historico',
] as $uri => $esperada) {
    $disparada = null;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $uri;
    $_SESSION['usuario']['id'] = 1;
    $router->dispatch();

    if ($disparada !== $esperada) {
        throw new RuntimeException("A rota {$uri} não foi encaminhada para a ação esperada.");
    }
}

$resolver = new ReflectionMethod(Router::class, 'encontrarRota');
$rotaDetalhe = $resolver->invoke($router, 'GET', '/api/v1/partidas/123');
if ($rotaDetalhe[0][1] !== 'showV1' || $rotaDetalhe[1] !== ['123']) {
    throw new RuntimeException('ID numérico deve continuar sendo encaminhado ao detalhe da partida.');
}
if ($rotas['POST']['/api/v1/partidas'][1] !== 'storeV1') {
    throw new RuntimeException('A rota POST V1 de criação deve chamar storeV1.');
}
if ($rotas['POST']['/api/partidas'][1] !== 'store') {
    throw new RuntimeException('A rota POST Web de criação deve chamar store.');
}
if ($rotas['POST']['/api/v1/partidas/{id}/pontuacoes'][1] !== 'registrarPontuacaoV1') {
    throw new RuntimeException('A rota de pontuação deve chamar a ação implementada.');
}
if ($rotas['POST']['/api/v1/partidas/{id}/arbitragem'][1] !== 'registrarArbitragemV1') {
    throw new RuntimeException('A rota de arbitragem deve chamar a ação implementada.');
}
if ($rotas['GET']['/api/v1/partidas/{id}/arbitragem/usuarios'][1] !== 'listarArbitrosElegiveisV1') {
    throw new RuntimeException('A rota deve listar os árbitros elegíveis para a partida.');
}

$openApi = file_get_contents(dirname(__DIR__) . '/openapi.yaml');
$partidasSpec = explode("\n  /calendario:", explode("\n  /partidas:", $openApi)[1])[0];
if (!str_contains($partidasSpec, 'operationId: criarPartida')) {
    throw new RuntimeException('OpenAPI deve documentar a criação de partidas.');
}

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/v1/partidas/ao-vivo';
Response::$payload = [];
Response::$status = 200;
$router->dispatch();
if (Response::$status !== 405 || Response::$payload['metodos_permitidos'] !== ['GET']) {
    throw new RuntimeException('Método incompatível deve retornar 405 e permitir GET.');
}

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/v1/partidas/rota-inexistente/detalhe';
Response::$payload = [];
Response::$status = 200;
$router->dispatch();
if (Response::$status !== 404) {
    throw new RuntimeException('Rota inexistente deve retornar 404.');
}

Response::$halt = true;
foreach ([$partidaV1, $partidaController] as $controller) {
    try {
        if ($controller === $partidaV1) {
            $controller->showV1('nao-numerico');
        } else {
            $controller->show('nao-numerico');
        }
        throw new RuntimeException('ID não numérico deveria ser rejeitado.');
    } catch (RouteTestResponseHalt $e) {
        if ($e->status !== 400) {
            throw new RuntimeException('ID inválido deve retornar 400.');
        }
    }
}

echo "Partida routes: OK\n";
