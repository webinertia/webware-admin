<?php

declare(strict_types=1);

namespace WebwareTest\Admin;

use ArrayIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Acl\AclInterface;
use Webware\Admin\Widget\AclWidgetFilterIterator;
use Webware\Admin\Widget\WidgetInterface;

use function iterator_to_array;

#[CoversClass(AclWidgetFilterIterator::class)]
final class AclWidgetFilterIteratorTest extends TestCase
{
    #[Test]
    public function itAcceptsWidgetWhenAclAllows(): void
    {
        self::markTestSkipped('Blocked on webware-usermanager split-out (UserInterface typing).');

        $widget = $this->makeWidget('admin.acl', 'read');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturn(true);

        $iterator = new AclWidgetFilterIterator(
            new ArrayIterator([$widget]),
            $acl,
            [AclInterface::DEVELOPER_ROLE_ID],
        );

        self::assertCount(1, iterator_to_array($iterator));
    }

    #[Test]
    public function itFiltersPartiallyAllowedWidgets(): void
    {
        self::markTestSkipped('Blocked on webware-usermanager split-out (UserInterface typing).');

        $allowed = $this->makeWidget('admin.dashboard', 'read');
        $denied  = $this->makeWidget('admin.acl', 'read');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturnMap([
            [['Administrator'], 'admin.dashboard', 'read', true],
            [['Administrator'], 'admin.acl', 'read', false],
        ]);

        $iterator = new AclWidgetFilterIterator(
            new ArrayIterator([$allowed, $denied]),
            $acl,
            ['Administrator'],
        );

        $results = iterator_to_array($iterator, preserve_keys: false);
        self::assertCount(1, $results);
        self::assertSame('admin.dashboard', $results[0]->resourceId);
    }

    #[Test]
    public function itRejectsNonWidgetItems(): void
    {
        self::markTestSkipped('Blocked on webware-usermanager split-out (UserInterface typing).');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturn(true);

        /** @var ArrayIterator<int, mixed> $inner */
        $inner    = new ArrayIterator(['not-a-widget']);
        $iterator = new AclWidgetFilterIterator($inner, $acl, [AclInterface::DEVELOPER_ROLE_ID]);

        self::assertCount(0, iterator_to_array($iterator));
    }

    #[Test]
    public function itRejectsWidgetWhenAclDenies(): void
    {
        self::markTestSkipped('Blocked on webware-usermanager split-out (UserInterface typing).');

        $widget = $this->makeWidget('admin.acl', 'read');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturn(false);

        $iterator = new AclWidgetFilterIterator(
            new ArrayIterator([$widget]),
            $acl,
            ['Administrator'],
        );

        self::assertCount(0, iterator_to_array($iterator));
    }

    private function makeWidget(string $resourceId, string $privilege): WidgetInterface
    {
        return new class($resourceId, $privilege) implements WidgetInterface {
            public string $title {
                get => 'Test';
            }

            public string $template {
                get => 'test::widget';
            }

            public int $order {
                get => 0;
            }

            public function __construct(
                public string $resourceId,
                public string $privilege,
            ) {}

            public function getResourceId(): string
            {
                return $this->resourceId;
            }
        };
    }
}
