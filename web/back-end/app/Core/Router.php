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

    public function dispatch(): void
    {
        $uri = parse_url(
            $_SERVER['REQUEST_URI'],
            PHP_URL_PATH
        );

        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        if (!is_string($uri)) {
            $uri = '/';
        }

        // Normaliza a URI.
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        $ehApi = $this->ehApi($uri);

        // Trata preflight CORS OPTIONS
        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        // 1. Procura a rota para o método solicitado.
        $rota = $this->encontrarRota($method, $uri);

        // 2. Verifica se o caminho existe com outro método.
        if ($rota === null) {
            $permitidos = $this->metodosPermitidos($uri);

            if ($permitidos !== []) {
                header('Allow: ' . implode(', ', $permitidos));

                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Método HTTP não permitido.',
                    'metodos_permitidos' => $permitidos
                ], 405);
            }

            // 3. Caminho inexistente: 404.
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Endpoint não encontrado.'
            ], 404);
        }

        // 4. Proteção CSRF para requisições mutantes (POST, PUT,
        //    PATCH e DELETE), antes de qualquer outro processamento.
        //    /login e /api/auth/login ficam isentos: o formulário de
        //    login é público e a sessão ainda não tem estado a proteger
        //    (login CSRF mitigado pela troca de ID de sessão no login).
        $rotaSemCsrf = $method === 'POST'
            && in_array($uri, ['/login', '/api/auth/login'], true);

        if (!$rotaSemCsrf && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            Csrf::exigirToken();
        }

        // 5. Verifica autenticação para rotas existentes.
        if (
            !$this->rotaPublica($method, $uri)
            && empty($_SESSION['usuario']['id'])
        ) {
            Response::json([
                'sucesso' => false,
                'mensagem' => 'Autenticação necessária.'
            ], 401);
        }

        // 6. Executa a ação da rota (callable ou [Controller, metodo]).
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
                ['/', '/api', '/api/status', '/login', '/logout', '/api/csrf'],
                true
            )
        );
    }
}
