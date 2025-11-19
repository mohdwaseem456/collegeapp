<?php
namespace Src\Core;

class Router {

    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => [],
    ];

    // ---------- REGISTER ROUTES ----------
    public function get(string $uri, array $options) {
        $this->routes['GET'][$uri] = $options;
    }

    public function post(string $uri, array $options) {
        $this->routes['POST'][$uri] = $options;
    }

    public function put(string $uri, array $options) {
        $this->routes['PUT'][$uri] = $options;
    }

    public function delete(string $uri, array $options) {
        $this->routes['DELETE'][$uri] = $options;
    }

    // ---------- DISPATCH ----------
    public function dispatch(string $uri, string $method) {

        session_start();

        // PUBLIC ROUTES
        $publicRoutes = [
            'POST' => ['/login'],
            'GET'  => ['/login']
        ];

        $isPublic = isset($publicRoutes[$method]) &&
                    in_array($uri, $publicRoutes[$method]);

        // LOGIN CHECK
        if (!$isPublic && empty($_SESSION['logged_in'])) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // ROUTE CHECK
        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            echo json_encode(['error' => 'Route not found']);
            return;
        }

        $route = $this->routes[$method][$uri];

        // ROLE CHECK
        if (!empty($route['auth'])) {
            if ($_SESSION['type'] != $route['auth']) {
                echo json_encode(['error' => 'Forbidden']);
                return;
            }
        }

        // ACTION EXECUTION
        $action = $route['action']; // string "Controller@method"
        [$class, $function] = explode('@', $action);

        $controller = new $class();
        return $controller->$function();
    }
}
