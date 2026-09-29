<?php

declare(strict_types=1);

namespace WebwareTest\Admin\Container;

use Mezzio\MiddlewareFactoryInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouteCollectorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Webware\Admin\AdminInterface;
use Webware\Admin\Container\Configuration;
use Webware\Admin\Container\RouteProviderFactory;

#[CoversClass(RouteProviderFactory::class)]
final class RouteProviderFactoryTest extends TestCase
{
    #[Test]
    public function itRegistersTheDashboardUnderTheDefaultNamespace(): void
    {
        self::assertSame(
            ['/admin', 'admin.dashboard.read'],
            $this->registeredRoute($this->container([])),
        );
    }

    #[Test]
    public function theRegisteredRouteFollowsAConfiguredNamespace(): void
    {
        self::assertSame(
            ['/control-panel', 'control-panel.dashboard.read'],
            $this->registeredRoute(
                $this->container([Configuration::ADMIN_NAME_KEY => 'control-panel']),
            ),
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private function container(array $config): ContainerInterface
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn([AdminInterface::class => $config]);

        return $container;
    }

    /**
     * @return array{string, string|null} the path and route name the provider registered
     */
    private function registeredRoute(ContainerInterface $container): array
    {
        /** @var array{string, string|null} $registered */
        $registered = [];

        $collector = $this->createMock(RouteCollectorInterface::class);
        $collector->expects($this->once())
            ->method('get')
            ->willReturnCallback(
                static function (
                    string $path,
                    MiddlewareInterface $middleware,
                    ?string $name = null,
                ) use (&$registered): Route {
                    $registered = [$path, $name];

                    return new Route($path, $middleware, ['GET'], $name);
                },
            );

        $middlewareFactory = $this->createStub(MiddlewareFactoryInterface::class);
        $middlewareFactory->method('prepare')
            ->willReturn(
                $this->createStub(MiddlewareInterface::class),
            );

        (new RouteProviderFactory())($container)->registerRoutes($collector, $middlewareFactory);

        return $registered;
    }
}
