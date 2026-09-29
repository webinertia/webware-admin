<?php

declare(strict_types=1);

namespace WebwareTest\Admin\View\Helper;

use Mezzio\Helper\UrlHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Webware\Admin\AdminInterface;
use Webware\Admin\Container\Configuration;
use Webware\Admin\View\Helper\AdminUrlFactory;

#[CoversClass(AdminUrlFactory::class)]
final class AdminUrlFactoryTest extends TestCase
{
    #[Test]
    public function theHelperPrefixesRouteNamesWithTheDefaultNamespace(): void
    {
        self::assertSame('/admin', $this->resolveUrl('dashboard.read', [], 'admin.dashboard.read'));
    }

    #[Test]
    public function thePrefixFollowsAConfiguredNamespace(): void
    {
        self::assertSame(
            '/admin',
            $this->resolveUrl(
                'dashboard.read',
                [Configuration::ADMIN_NAME_KEY => 'control-panel'],
                'control-panel.dashboard.read',
            ),
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private function container(array $config, UrlHelper $urlHelper): ContainerInterface
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')
            ->willReturnMap([
                [UrlHelper::class, $urlHelper],
                ['config', [AdminInterface::class => $config]],
            ]);

        return $container;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function resolveUrl(string $routeName, array $config, string $expectedRoute): string
    {
        $urlHelper = $this->createMock(UrlHelper::class);
        $urlHelper->expects($this->once())
            ->method('__invoke')
            ->with($expectedRoute)
            ->willReturn('/admin');

        return (new AdminUrlFactory())($this->container($config, $urlHelper))($routeName);
    }
}
