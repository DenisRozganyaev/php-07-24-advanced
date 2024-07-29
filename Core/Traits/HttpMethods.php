<?php

namespace Core\Traits;

use App\Enums\Http\Method;

trait HttpMethods
{
    static public function get(string $uri): static
    {
        return static::setUri($uri)->setMethod(Method::GET);
    }

    static public function post(string $uri): static
    {
        return static::setUri($uri)->setMethod(Method::POST);
    }

    static public function put(string $uri): static
    {
        return static::setUri($uri)->setMethod(Method::PUT);
    }

    static public function delete(string $uri): static
    {
        return static::setUri($uri)->setMethod(Method::DELETE);
    }

    protected function setMethod(Method $method): static
    {
        $this->routes[$this->currentRoute]['method'] = $method->value;
        return $this;
    }
}
