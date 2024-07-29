<?php

namespace Core;

use App\Enums\Http\Status;
use Core\Traits\HttpMethods;

class Router
{
    use HttpMethods;

    static protected Router|null $instance = null;

    /**
     * @var array $routes = [
     *      'users/{id:\d+}/edit' => [
     *          'controller' => UsersController::class,
     *          'action' => 'edit'
     *          'method' => 'GET'
     *          'id' => 4
     *      ]
     * ]
     */
    protected array $routes = [], $params = [];

    protected array $convertTypes = [
        'd' => 'int',
        '.' => 'string'
    ];

    /**
     * @var string $currentRoute = 'users/{id:\d+}/edit'
     */
    protected string $currentRoute;

    static public function getInstance(): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    static protected function setUri(string $uri): static
    {
        // 'users\\/{id:\d+}\\/edit'
        $uri = preg_replace('/\//', '\\/', $uri);
        // 'users\\/{id:\d+}\\/edit'
        $uri = preg_replace('/\{([a-zA-Z_-]+):([^}]+)}/', '(?P<$1>$2)', $uri);
        // ['id' => 4]
        $uri = "/^$uri$/i";

        $router = static::getInstance();
        $router->routes[$uri] = [];
        $router->currentRoute = $uri;

        return $router;
    }

    static public function dispatch(string $uri): string
    {
        $router = static::getInstance();
        $uri = $router->removeQueryVariables($uri);
        $uri = trim($uri, '/');

        if ($router->match($uri)) {
            $router->checkHttpMethod();
            $controller = new $router->params['controller'];
            $action = $router->params['action'];

            unset($router->params['controller']);
            unset($router->params['action']);

            if ($controller->before($action, $router->params)) {
                $response = call_user_func_array([$controller, $action], $router->params);
                $controller->after($action, $response);

                if ($response) {
                    return jsonResponse(
                        $response['status'],
                        [
                            'data' => $response['body'],
                            'errors' => $response['errors']
                        ]
                    );
                }
            }
        }

        return jsonResponse(
            Status::INTERNAL_SERVER_ERROR,
            [
                'data' => [],
                'errors' => 'Empty response'
            ]
        );
    }

    public function controller(string $controller): static
    {
        if (!class_exists($controller)) {
            throw new \Exception("Controller $controller does not exists!");
        }

        if (get_parent_class($controller) !== Controller::class) {
            throw new \Exception("Controller $controller does not extend " . Controller::class);
        }

        $this->routes[$this->currentRoute]['controller'] = $controller;
        return $this;
    }

    public function action(string $action): void
    {
        if (empty($this->routes[$this->currentRoute]['controller'])) {
            throw new \Exception("Route does not have controller value");
        }

        $controller = $this->routes[$this->currentRoute]['controller'];

        if (!method_exists($controller, $action)) {
            throw new \Exception("[$controller] does not contain [$action] action");
        }

        $this->routes[$this->currentRoute]['action'] = $action;
    }

    protected function checkHttpMethod(): void
    {
        $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

        if ($requestMethod !== strtolower($this->params['method'])) {
            throw new \Exception("Method [$requestMethod] is not allowed for this route", Status::METHOD_NOT_ALLOWED->value);
        }

        unset($this->params['method']);
    }

    protected function match(string $uri): bool
    {
        foreach($this->routes as $regex => $params) {
            if (preg_match($regex, $uri, $matches)) {
                /**
                 * $matches = [
                 *  0 => full regex result
                 *  'id' => 42
                 *  1 => 42
                 * ]
                 */
                $this->params = $this->buildParams($regex, $matches, $params);
                /**
                 * [
                 *  'controller' => ...,
                 *  'action' => ...,
                 * .....
                 *  'id' => 42
                 * ]
                 */
                return true;
            }
        }
        throw new \Exception(__CLASS__ . ": Route [$uri] not found", Status::NOT_FOUND->value);
    }

    protected function buildParams(string $regex, array $matches, array $params): array
    {
        preg_match_all('/\(\?P<[\w]+>\\\\?([\w\.][\+]*)\)/', $regex, $types);
        $uriParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        if (!empty($types)) {
            $lastKey = array_key_last($types);
            $step = 0;
            $types = array_map(
                fn ($value) => str_replace('+', '', $value),
                $types[$lastKey]
            );
            /**
             * ['id' => '42', 'note_id' => '55']
             */
            /**
             * ['d', 'd']
             */
            foreach ($uriParams as $key => $value) {
                settype($value, $this->convertTypes[$types[$step]]);
                $params[$key] = $value;
                $step++;
            }
            /**
             * ['id' => 42, 'note_id' => 55]
             */
        }

        return $params;
    }


    protected function removeQueryVariables(string $uri): string
    {
        return preg_replace('/([\w\/\d]+)(\?[\w=\d\&\%\[\]\-\_\:\+\"\"\'\']+)/i', '$1', $uri);
    }
}
