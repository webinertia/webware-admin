<?php

declare(strict_types=1);

namespace WebwareTest\Admin\Container;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Webware\Admin\AdminInterface;
use Webware\Admin\Container\Configuration;
use Webware\Core\Exception\ContainerException;

#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase
{
    #[Test]
    public function aConfiguredAdminNameRelocatesTheWholeNamespace(): void
    {
        $container = $this->container([Configuration::ADMIN_NAME_KEY => 'control-panel']);

        self::assertSame('control-panel', Configuration::getAdminName($container, self::class));
        self::assertSame('control-panel.', Configuration::getAdminNamePrefix($container, self::class));
        self::assertSame('control-panel', Configuration::getAdminSegment($container, self::class));
    }

    #[Test]
    public function anUnusableConfiguredValueFallsBackToTheDefault(): void
    {
        foreach ([null, '', 42, ['admin']] as $value) {
            $container = $this->container([Configuration::ADMIN_NAME_KEY => $value]);

            self::assertSame(
                Configuration::ADMIN_NAME,
                Configuration::getAdminName($container, self::class),
            );
        }
    }

    #[Test]
    public function itThrowsWhenTheConfigServiceIsMissing(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $this->expectException(ContainerException::class);

        Configuration::getAdminName($container, self::class);
    }

    #[Test]
    public function theAdminNameDefaultsToAdmin(): void
    {
        self::assertSame('admin', Configuration::getAdminName($this->container([]), self::class));
    }

    #[Test]
    public function theDefaultNamespaceIsTheAdminPrefixAndSegment(): void
    {
        $container = $this->container([]);

        self::assertSame('admin.', Configuration::getAdminNamePrefix($container, self::class));
        self::assertSame('admin', Configuration::getAdminSegment($container, self::class));
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
}
