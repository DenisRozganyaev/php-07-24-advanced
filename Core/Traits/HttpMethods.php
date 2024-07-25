<?php

namespace Core\Traits;

trait HttpMethods
{
    static public function get(string $uri): static
    {
        return static::setUri($uri);
    }

    static public function post(string $uri)
    {

    }

    static public function put(string $uri)
    {

    }

    static public function delete(string $uri)
    {

    }
}
