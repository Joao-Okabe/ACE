<?php

class Router
{
    private array $routes = [];

    public function get(string $uri, array|callable $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array|callable $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, array|callable $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function patch(string $uri, array|callable $action): void
    {
        $this->addRoute('PATCH', $uri, $action);
    }

    public function delete(string $uri, array|callable $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    private function addRoute(
        string $method,
        string $uri,
        array|callable $action
    ): void {
        $this->routes[$method][$uri] = $action;
    }

    private function encontrarRota(
        string $method,
        string $uri
    ): ?array {
        foreach ($this->routes[$method] ?? [] as $rota => $action) {
            $segmentos = explode('/', trim($rota, '/'));
            $padroes = [];

            foreach ($segmentos as $segmento) {
                if (preg_match(
                    '/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/',
                    $segmento,
                    $match
                )) {
                    $padroes[] = '(?P<' . $match[1] . '>[^/]+)';
                } else {
                    $padroes[] = preg_quote($segmento, '#');
                }
            }

            $padrao = $rota === '/'
                ? '#^/$#'
                : '#^/' . implode('/', $padroes) . '$#';

            if (preg_match($padrao, $uri, $matches)) {
                $parametros = [];

                foreach ($matches as $chave => $valor) {
                    if (is_string($chave)) {
                        $parametros[] = rawurldecode($valor);
                    }
                }

                return [$action, $parametros];
            }
        }

        return null;
    }

    private function metodosPermitidos(string $uri): array
    {
        $permitidos = [];

        foreach ($this->routes as $method => $rotas) {
            if ($this->encontrarRota($method, $uri) !== null) {
                $permitidos[] = $method;
            }
        }

        return $permitidos;
    }

    private function ehApi(string $uri): bool
    {
        return $uri === '/api'
            || str_starts_with($uri, '/api/');
    }

    private function ehApiV1(string $uri): bool
    {
        return $uri === '/api/v1'
            || str_starts_with($uri, '/api/v1/');
    }

    public function dispatch(): void
    {
        AuthContext::limpar();

        $uri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $method = strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );

        if (!is_string($uri) || $uri === '') {
            $uri = '/';
        }

        // Normaliza a URI.
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        $ehApi = $this->ehApi($uri);
        $ehApiV1 = $this->ehApiV1($uri);

        /*
        |--------------------------------------------------------------------------
        | Preflight
        |--------------------------------------------------------------------------
        */

        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Localização da rota
        |--------------------------------------------------------------------------
        */

        $rota = $this->encontrarRota($method, $uri);

        if ($rota === null) {
            $permitidos = $this->metodosPermitidos($uri);

            if ($permitidos !== []) {
                header('Allow: ' . implode(', ', $permitidos));

                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Método HTTP não permitido.',
                    'metodos_permitidos' => $permitidos
                ], 405);

                return;
            }

            Response::json([
                'sucesso' => false,
                'mensagem' => 'Endpoint não encontrado.'
            ], 404);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Autenticação e proteção das APIs
        |--------------------------------------------------------------------------
        */

        if ($ehApiV1) {
            // Rotas públicas da autenticação JWT.
            $rotasPublicasV1 = [
                '/api/v1/auth/login',
                '/api/v1/auth/refresh',
                '/api/v1/auth/logout'
            ];

            $rotaPublicaV1 = $method === 'POST'
                && in_array($uri, $rotasPublicasV1, true);

            if (!$rotaPublicaV1) {
                if (!JwtMiddleware::autenticar()) {
                    return;
                }
            }
        } else {
            /*
             * Web e API legada:
             * mantêm CSRF e autenticação por sessão.
             */

            $rotaSemCsrf = $method === 'POST'
                && in_array(
                    $uri,
                    ['/login', '/api/auth/login'],
                    true
                );

            if (
                !$rotaSemCsrf
                && in_array(
                    $method,
                    ['POST', 'PUT', 'PATCH', 'DELETE'],
                    true
                )
            ) {
                Csrf::exigirToken();
            }

            if (
                !$this->rotaPublica($method, $uri)
                && empty($_SESSION['usuario']['id'])
            ) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Autenticação necessária.'
                ], 401);

                return;
            }

            // Disponibiliza a sessão no contexto unificado.
            if (!empty($_SESSION['usuario']['id'])) {
                AuthContext::definir($_SESSION['usuario']);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Execução da rota
        |--------------------------------------------------------------------------
        */

        [$action, $parametros] = $rota;

        if (is_callable($action)) {
            $action(...$parametros);
            return;
        }

        [$controller, $metodo] = $action;

        $controllerInstance = new $controller();
        $controllerInstance->$metodo(...$parametros);
    }

    private function rotaPublica(
        string $method,
        string $uri
    ): bool {
        return (
            $method === 'POST'
            && in_array(
                $uri,
                ['/login', '/api/auth/login', '/api/usuarios'],
                true
            )
        ) || (
            $method === 'GET'
            && in_array(
                $uri,
                [
                    '/',
                    '/api',
                    '/api/status',
                    '/login',
                    '/logout',
                    '/api/csrf'
                ],
                true
            )
        );
    }
}
