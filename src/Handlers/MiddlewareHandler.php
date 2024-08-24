<?php

declare(strict_types=1);

namespace Atyalpa\Routing\Handlers;

use Closure;
use InvalidArgumentException;

class MiddlewareHandler
{
    /**
     * @param  array<string|Closure>  $middlewares
     */
    public function __construct(protected array $middlewares) {}

    public function handle(): array
    {
        return array_map(
            /**
             * @throws InvalidArgumentException
             */
            function (string|Closure $middleware) {
                if (is_callable($middleware)) {
                    return $middleware;
                }

                if (is_string($middleware) && class_exists($middleware)) {
                    return new $middleware;
                }

                throw new InvalidArgumentException(
                    'Middleware should be a callable or a class FQN or a Closure'
                );
            },
            $this->middlewares
        );
    }
}
