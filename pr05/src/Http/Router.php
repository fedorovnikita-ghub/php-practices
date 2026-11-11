<?php
declare(strict_types=1);

namespace Course\Http;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = strtoupper($request->method);
        $path = $request->path;
        $allowedMethods = [];
        $routeMatched = false;

        foreach ($this->routes as $route) {
            // Экранируем слэши в путях и заменяем {id} на цифры
            $pattern = preg_quote($route['path'], '#');
            $pattern = str_replace('\{id\}', '([1-9][0-9]{0,8})', $pattern);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                $routeMatched = true;
                $allowedMethods[] = $route['method'];
                if ($route['method'] === 'GET') {
                    $allowedMethods[] = 'HEAD';
                }

                $currentAllowed = [$route['method']];
                if ($route['method'] === 'GET') {
                    $currentAllowed[] = 'HEAD';
                }

                if (in_array($method, $currentAllowed, true)) {
                    $id = isset($matches[1]) ? (int) $matches[1] : null;
                    if ($id !== null) {
                        return ($route['handler'])($request, $id);
                    }
                    return ($route['handler'])($request);
                }
            }
        }

        if ($routeMatched) {
            $allowedMethods = array_unique($allowedMethods);
            sort($allowedMethods);
            throw new HttpError(405, 'method_not_allowed', 'Метод не разрешен', [], [
                'Allow' => implode(', ', $allowedMethods)
            ]);
        }

        throw new HttpError(404, 'not_found', 'Страница не найдена');
    }
}