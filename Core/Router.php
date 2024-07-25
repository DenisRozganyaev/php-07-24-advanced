<?php

namespace Core;

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
}
