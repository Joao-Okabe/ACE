<?php

class Router
{
    private array $routes = [];

    public function get(string $uri, array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, array $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function delete(string $uri, array $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    private function addRoute(
        string $method,
        string $uri,
        array $action
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

        // 1. Procura a rota para o método solicitado.
        $rota = $this->encontrarRota($method, $uri);

        // 2. Verifica se o caminho existe com outro método.
        if ($rota === null) {
            $permitidos = $this->metodosPermitidos($uri);

            if ($permitidos !== []) {
                header('Allow: ' . implode(', ', $permitidos));

                if ($ehApi) {
                    Response::json([
                        'sucesso' => false,
                        'mensagem' => 'Método HTTP não permitido.',
                        'metodos_permitidos' => $permitidos
                    ], 405);
                }

                http_response_code(405);
                echo 'Método HTTP não permitido.';
                return;
            }

            // 3. Caminho inexistente: 404.
            if ($ehApi) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Endpoint não encontrado.'
                ], 404);
            }

            http_response_code(404);
            require __DIR__ . '/../Views/error/404.php';
            return;
        }

        // 4. Verifica autenticação para rotas existentes.
        if (
            !$this->rotaPublica($method, $uri)
            && empty($_SESSION['usuario']['id'])
        ) {
            if ($ehApi) {
                Response::json([
                    'sucesso' => false,
                    'mensagem' => 'Autenticação necessária.'
                ], 401);
            }

            header('Location: /login');
            exit;
        }

        // 5. Executa o controller.
        [$action, $parametros] = $rota;
        [$controller, $metodo] = $action;

        $controllerInstance = new $controller();
        $controllerInstance->$metodo(...$parametros);
    }

    private function rotaPublica(
        string $method,
        string $uri
    ): bool {
        return (
            $method === 'GET'
            && in_array(
                $uri,
                ['/','/login','/logout'],
                true
            )
        ) || (
            $method === 'POST'
            && $uri === '/login'
        );
    }
}
