<?php

namespace Handlers;

use Atyalpa\Routing\Handlers\MiddlewareHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(MiddlewareHandler::class)]
class MiddlewareHandlerTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated(): void
    {
        $handler = new MiddlewareHandler([]);
        $this->assertInstanceOf(MiddlewareHandler::class, $handler);
    }

    /**
     * @throws \Exception
     */
    #[Test]
    public function it_returns_a_passed_closure_with_handle(): void
    {
        $closure = fn () => 'Execute as a Middleware';

        $handler = new MiddlewareHandler([$closure]);

        $this->assertSame([$closure], $handler->handle());
    }

    #[Test]
    public function it_returns_an_object_of_passed_class_with_handle(): void
    {
        $middleware = $this->createMock(stdClass::class);
        $middlewareClassName = get_class($middleware);

        $handler = new MiddlewareHandler([$middlewareClassName]);
        $middlewares = $handler->handle();

        $this->assertIsArray($middlewares);

        $this->assertInstanceOf($middlewareClassName, $middlewares[0]);
    }

    #[Test]
    public function it_throws_an_exception_if_passed_middleware_is_neither_a_closure_nor_a_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $handler = new MiddlewareHandler(['some-random-middleware']);

        $handler->handle();
    }
}
