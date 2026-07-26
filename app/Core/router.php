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


    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];


        if(isset($this->routes[$method][$uri])) {

            [$controller, $action] = $this->routes[$method][$uri];

            $controllerInstance = new $controller();

            $controllerInstance->$action();

            return;
        }


        http_response_code(404);
        echo "Página não encontrada";
    }
}