<?php

declare(strict_types=1);

namespace WebwareTestIntegration\Admin;

use Laminas\Permissions\Acl\AclInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Admin\ConfigProvider;
use WebwareTestIntegration\Admin\TestAssets\ExpectedConfig;

#[CoversClass(ConfigProvider::class)]
#[CoversMethod(ConfigProvider::class, '__invoke')]
#[CoversMethod(ConfigProvider::class, 'getAclConfig')]
#[CoversMethod(ConfigProvider::class, 'getDefaultConfig')]
#[CoversMethod(ConfigProvider::class, 'getDependencies')]
#[CoversMethod(ConfigProvider::class, 'getRouteProviders')]
#[CoversMethod(ConfigProvider::class, 'getTemplates')]
#[CoversMethod(ConfigProvider::class, 'getViewHelpers')]
final class ConfigProviderIntegrationTest extends TestCase
{
    #[Test]
    public function configProviderCanBeInvokedMultipleTimes(): void
    {
        $configProvider = new ConfigProvider();
        $config1        = $configProvider();
        $config2        = $configProvider();

        static::assertSame($config1, $config2);
    }

    #[Test]
    public function configProviderDoesNotRegisterTheAclService(): void
    {
        $config = (new ConfigProvider())();

        // The ACL implementation itself is supplied by the consuming
        // application (e.g. webware-acl aliased to the laminas AclInterface);
        // admin only ships the authorization rules above.
        self::assertArrayNotHasKey(AclInterface::class, $config);
    }

    #[Test]
    public function getAclConfigReturnsExpectedConfig(): void
    {
        static::assertSame(ExpectedConfig::getExpectedAclConfig(), new ConfigProvider()->getAclConfig());
    }

    #[Test]
    public function getDefaultConfigReturnsExpectedConfig(): void
    {
        static::assertSame(ExpectedConfig::getExpectedDefaultConfig(), new ConfigProvider()->getDefaultConfig());
    }

    #[Test]
    public function getDependenciesReturnsExpectedConfig(): void
    {
        static::assertSame(ExpectedConfig::getExpectedDependencies(), new ConfigProvider()->getDependencies());
    }

    #[Test]
    public function getRouteProvidersReturnsExpectedConfig(): void
    {
        static::assertSame(ExpectedConfig::getExpectedRouteProviders(), new ConfigProvider()->getRouteProviders());
    }

    #[Test]
    public function getTemplatesReturnsExpectedConfig(): void
    {
        static::assertSame(ExpectedConfig::getExpectedTemplates(), new ConfigProvider()->getTemplates());
    }

    #[Test]
    public function getViewHelpersReturnsExpectedConfig(): void
    {
        static::assertSame(ExpectedConfig::getExpectedViewHelpers(), new ConfigProvider()->getViewHelpers());
    }

    #[Test]
    public function invokeReturnsExpectedConfig(): void
    {
        static::assertSame(ExpectedConfig::getExpectedConfig(), (new ConfigProvider())());
    }
}
