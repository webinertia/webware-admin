<?php

declare(strict_types=1);

namespace WebwareTestIntegration\Admin;

use Laminas\Permissions\Acl\AclInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Admin\ConfigProvider;
use Webware\Admin\Container\Configuration;

#[CoversClass(ConfigProvider::class)]
final class ConfigProviderIntegrationTest extends TestCase
{
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
    public function configProviderProvidesMezzioAclDefaultsForTheAdminRoutes(): void
    {
        $config = (new ConfigProvider())();

        self::assertArrayHasKey('mezzio-authorization-acl', $config);

        $aclConfig = $config['mezzio-authorization-acl'];
        $dashboard = Configuration::ADMIN_ROUTE_NAME_PREFIX_VALUE . 'dashboard.read';

        // Matches the structure consumed by mezzio/mezzio-authorization-acl:
        // resources are the route names registered by the admin RouteProvider.
        self::assertSame(['User' => [], 'Administrator' => ['User']], $aclConfig['roles']);
        self::assertSame([$dashboard], $aclConfig['resources']);
        self::assertSame(['Administrator' => [$dashboard]], $aclConfig['allow']);
    }

    #[Test]
    public function configProviderReturnsExpectedTopLevelKeys(): void
    {
        $config = (new ConfigProvider())();

        self::assertArrayHasKey('dependencies', $config);
        self::assertArrayHasKey('templates', $config);
        self::assertArrayHasKey('view_helpers', $config);
    }
}
