<?php

declare(strict_types=1);

namespace WebwareTest\Admin\Acl;

use Mezzio\MiddlewareFactoryInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouteCollectorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Webware\Admin\Acl\RuleSeeds;
use Webware\Admin\RouteProvider;
use Webware\Core\Acl\RuleSeed;
use Webware\Core\Acl\RuleType;
use Webware\Core\Role;

use function array_map;

#[CoversClass(RuleSeeds::class)]
#[CoversMethod(RuleSeeds::class, 'ruleSeeds')]
final class RuleSeedsTest extends TestCase
{
    /**
     * A rule can only grant access to a route that exists, so every seeded resource id must be a
     * name the RouteProvider registers.
     */
    #[Test]
    public function everySeededResourceIdIsARegisteredRouteName(): void
    {
        /** @var list<string|null> $names */
        $names = [];

        $collector = $this->createStub(RouteCollectorInterface::class);
        $collector->method('get')
            ->willReturnCallback(
                static function (string $path, MiddlewareInterface $mw, ?string $name = null) use (&$names): Route {
                    $names[] = $name;

                    return new Route($path, $mw, ['GET'], $name);
                },
            );

        $factory = $this->createStub(MiddlewareFactoryInterface::class);
        $factory->method('prepare')->willReturn($this->createStub(MiddlewareInterface::class));

        new RouteProvider('admin', 'admin.')->registerRoutes($collector, $factory);

        self::assertEqualsCanonicalizing(
            $names,
            array_map(static fn(RuleSeed $seed): string => $seed->resourceId, new RuleSeeds()->ruleSeeds('admin')),
        );
    }

    #[Test]
    public function followsTheAdminNameTheApplicationChooses(): void
    {
        self::assertSame('manage.dashboard.read', new RuleSeeds()->ruleSeeds('manage')[0]->resourceId);
    }

    #[Test]
    public function grantsTheDashboardToAdministrator(): void
    {
        $seeds = new RuleSeeds()->ruleSeeds('admin');

        self::assertCount(1, $seeds);
        self::assertSame(RuleType::Allow, $seeds[0]->type);
        self::assertSame(Role::Administrator->value, $seeds[0]->roleId);
        self::assertSame('admin.dashboard.read', $seeds[0]->resourceId);
    }
}
