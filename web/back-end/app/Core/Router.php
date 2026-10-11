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

    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Normaliza a URI, removendo a barra final.
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        // 1. Procura a rota antes de verificar a autenticação.
        $rota = $this->encontrarRota($method, $uri);

        // 2. Se a rota não existir, retorna 404.
        if ($rota === null) {
            http_response_code(404);
            require __DIR__ . '/../Views/error/404.php';
            return;
        }

        // 3. Verifica a autenticação somente para rotas existentes.
        if (
            !$this->rotaPublica($method, $uri)
            && empty($_SESSION['usuario']['id'])
        ) {
            header('Location: /login');
            exit;
        }

        // 4. Executa a rota encontrada.
        [$action, $parametros] = $rota;
        [$controller, $metodo] = $action;

        $controllerInstance = new $controller();
        $controllerInstance->$metodo(...$parametros);
    }

    private function rotaPublica(string $method, string $uri): bool
    {
        return $method === 'GET' && in_array($uri, ['/', '/login', '/logout'], true)
            || $method === 'POST' && $uri === '/login';
    }
}