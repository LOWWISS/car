<?php
/**
 * Router: clean-URL routing to controller@method.
 *
 * Routes are registered as: $router->add('GET', '/cars/view/{id}', 'CarController@show')
 * URL params ({id}) are passed as method arguments. All matching is exact per
 * segment; the request path is normalized in Request::path().
 */
final class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, string $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler, // "Controller@method"
        ];
    }

    /** Convenience: register multiple methods for one route. */
    public function any(string $pattern, string $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $m) {
            $this->add($m, $pattern, $handler);
        }
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $path = '/' . trim($path, '/');
        if ($path === '/') $path = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $params = $this->match($route['pattern'], $path);
            if ($params !== false) {
                $this->invoke($route['handler'], $params);
                return;
            }
        }
        Response::notFound('The page you requested could not be found.');
    }

    private function match(string $pattern, string $path): array|false
    {
        $pattern = '/' . trim($pattern, '/');
        if ($pattern === '/') $pattern = '/';

        $pSeg = explode('/', trim($pattern, '/'));
        $xSeg = explode('/', trim($path, '/'));
        if ($pattern === '/' ) $pSeg = [];
        if ($path === '/') $xSeg = [];
        if (count($pSeg) !== count($xSeg)) return false;

        $params = [];
        for ($i = 0; $i < count($pSeg); $i++) {
            $p = $pSeg[$i];
            $x = $xSeg[$i];
            if (str_starts_with($p, '{') && str_ends_with($p, '}')) {
                $name = trim($p, '{}');
                $params[] = $x;
            } elseif ($p !== $x) {
                return false;
            }
        }
        return $params;
    }

    private function invoke(string $handler, array $params): void
    {
        [$class, $method] = explode('@', $handler, 2);
        if (!class_exists($class) || !method_exists($class, $method)) {
            Response::serverError("Handler not found: {$handler}");
        }
        $controller = new $class();
        $controller->{$method}(...$params);
    }
}
