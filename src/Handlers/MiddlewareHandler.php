<?php

declare(strict_types=1);

namespace Atyalpa\Routing\Handlers;

use Closure;
use Exception;

class MiddlewareHandler
{
    public function __construct(protected array $middlewares)
    {}

    public function handle(): array
    {
        return array_map(
            /**
             * @throws Exception
             */
            function (string|Closure $middleware) {
                if (is_callable($middleware)) {
                    return $middleware;
                }

                if (is_string($middleware) && class_exists($middleware)) {
                    return new $middleware;
                }

                throw new Exception('Middleware should be a callable or a class FQN or a Closure');
            },
            $this->middlewares
        );
    }
}