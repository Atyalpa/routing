<?php

use Atyalpa\Routing\Router;
use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[\PHPUnit\Framework\Attributes\CoversClass(Router::class)]
class RouterTest extends TestCase
{
    private Router $router;

    public function setUp(): void
    {
        $this->router = new Router;
    }

    public static function provideHttpMethods(): array
    {
        return [
            'With GET HTTP method' => ['GET'],
            'With POST HTTP method' => ['POST'],
            'With PUT HTTP method' => ['PUT'],
            'With PATCH HTTP method' => ['PATCH'],
            'With DELETE HTTP method' => ['DELETE'],
        ];
    }

    #[
        Test,
        DataProvider('provideHttpMethods')
    ]
    public function it_creates_a_route(string $method): void
    {
        $closure = fn () => 'This is a route';
        $this->router->{$method}('/sample', $closure);

        $this->assertSame(
            [
                Dispatcher::FOUND,
                [
                    'controller' => $closure,
                    'middleware' => [],
                ],
                []
            ],
            $this->router->dispatch($method, '/sample')
        );
    }

    #[
        Test,
        DataProvider('provideHttpMethods')
    ]
    public function it_creates_a_route_with_route_parameters(string $method): void
    {
        $closure = fn () => 'This is a route';
        $this->router->{$method}('/sample/{param}', $closure);

        $this->assertSame(
            [
                Dispatcher::FOUND,
                [
                    'controller' => $closure,
                    'middleware' => [],
                ],
                [
                    'param' => 'some-param'
                ]
            ],
            $this->router->dispatch($method, '/sample/some-param')
        );
    }

    #[
        Test,
        DataProvider('provideHttpMethods')
    ]
    public function it_creates_empty_array_for_invalid_route(string $method): void
    {
        $closure = fn () => 'This is a route';
        $this->router->{$method}('/sample/{param}', $closure);

        $this->assertSame(
            [Dispatcher::NOT_FOUND],
            $this->router->dispatch($method, '/invalid-route/some-param')
        );
    }

    #[
        Test,
        DataProvider('provideHttpMethods')
    ]
    public function it_adds_prefix_to_the_route(string $method): void
    {
        $closure = fn () => 'This is a route';
        $this->router->prefix('prefix')->{$method}('/sample/{param}', $closure);

        $this->assertSame(
            [
                Dispatcher::FOUND,
                [
                    'controller' => $closure,
                    'middleware' => [],
                ],
                [
                    'param' => 'some-param'
                ]
            ],
            $this->router->dispatch($method, 'prefix/sample/some-param')
        );
    }

    #[
        Test,
        DataProvider('provideHttpMethods')
    ]
    public function it_adds_middleware_to_the_route(string $method): void
    {
        $closure = fn () => 'This is a route';
        $this->router->middleware([
            'Middleware_1_Class',
            'Middleware_2_Class',
        ])
            ->{$method}('/sample/{param}', $closure);

        $this->assertSame(
            [
                Dispatcher::FOUND,
                [
                    'controller' => $closure,
                    'middleware' => [
                        'Middleware_1_Class',
                        'Middleware_2_Class',
                    ],
                ],
                [
                    'param' => 'some-param'
                ]
            ],
            $this->router->dispatch($method, '/sample/some-param')
        );
    }

    #[
        Test,
        DataProvider('provideHttpMethods')
    ]
    public function it_creates_a_route_group_having_same_prefix_and_middleware(string $method): void
    {
        $closure = fn () => 'This is a route';
        $this->router->middleware(['MiddlewareClass'])
            ->prefix('some-prefix')
            ->group(function (Router $router) use ($method, $closure) {
                $router->{$method}('sample_1', $closure);
                $router->{$method}('sample_2', $closure);
            });

        $this->assertSame(
            [
                Dispatcher::FOUND,
                [
                    'controller' => $closure,
                    'middleware' => ['MiddlewareClass'],
                ],
                []
            ],
            $this->router->dispatch($method, 'some-prefix/sample_1')
        );

        $this->assertSame(
            [
                Dispatcher::FOUND,
                [
                    'controller' => $closure,
                    'middleware' => ['MiddlewareClass'],
                ],
                []
            ],
            $this->router->dispatch($method, 'some-prefix/sample_2')
        );
    }
}