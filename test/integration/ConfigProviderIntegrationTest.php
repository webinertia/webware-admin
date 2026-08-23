<?php

declare(strict_types=1);

namespace WebwareTestIntegration\Admin;

use Laminas\Permissions\Acl\AclInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Admin\ConfigProvider;

#[CoversClass(ConfigProvider::class)]
final class ConfigProviderIntegrationTest extends TestCase
{
    #[Test]
    public function configProviderDoesNotOwnTheAclService(): void
    {
        $config = (new ConfigProvider())();

        // The ACL implementation is supplied by the consuming application
        // (e.g. webware-acl aliased to Laminas\Permissions\Acl\AclInterface);
        // admin must not configure it itself.
        self::assertArrayNotHasKey(AclInterface::class, $config);
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
