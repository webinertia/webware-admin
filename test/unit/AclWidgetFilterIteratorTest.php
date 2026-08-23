<?php

declare(strict_types=1);

namespace WebwareTest\Admin;

use ArrayIterator;
use Laminas\Permissions\Acl\AclInterface;
use Laminas\Permissions\Acl\Role\GenericRole;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Admin\Widget\AclWidgetFilterIterator;
use Webware\Admin\Widget\WidgetInterface;

use function iterator_to_array;

#[CoversClass(AclWidgetFilterIterator::class)]
final class AclWidgetFilterIteratorTest extends TestCase
{
    #[Test]
    public function itAcceptsWidgetWhenAclAllows(): void
    {
        $widget = $this->makeWidget('admin.acl', 'read');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturn(true);

        $iterator = new AclWidgetFilterIterator(
            new ArrayIterator([$widget]),
            $acl,
            new GenericRole('Administrator'),
        );

        self::assertCount(1, iterator_to_array($iterator));
    }

    #[Test]
    public function itDeniesAllWidgetsWhenUserIsNull(): void
    {
        $widget = $this->makeWidget('admin.acl', 'read');

        $acl = $this->createMock(AclInterface::class);
        $acl->expects(self::never())
            ->method('isAllowed');

        $iterator = new AclWidgetFilterIterator(
            new ArrayIterator([$widget]),
            $acl,
            null,
        );

        self::assertCount(0, iterator_to_array($iterator));
    }

    #[Test]
    public function itFiltersPartiallyAllowedWidgets(): void
    {
        $allowed = $this->makeWidget('admin.dashboard', 'read');
        $denied  = $this->makeWidget('admin.acl', 'read');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturnCallback(
            static fn(mixed $role, mixed $resource, mixed $privilege): bool => 'admin.dashboard' === $resource,
        );

        $iterator = new AclWidgetFilterIterator(
            new ArrayIterator([$allowed, $denied]),
            $acl,
            new GenericRole('Administrator'),
        );

        $results = iterator_to_array($iterator, preserve_keys: false);
        self::assertCount(1, $results);
        self::assertSame('admin.dashboard', $results[0]->resourceId);
    }

    #[Test]
    public function itRejectsNonWidgetItems(): void
    {
        $acl = $this->createMock(AclInterface::class);
        $acl->expects(self::never())
            ->method('isAllowed');

        /** @var ArrayIterator<int, mixed> $inner */
        $inner    = new ArrayIterator(['not-a-widget']);
        $iterator = new AclWidgetFilterIterator($inner, $acl, new GenericRole('Administrator'));

        self::assertCount(0, iterator_to_array($iterator));
    }

    #[Test]
    public function itRejectsWidgetWhenAclDenies(): void
    {
        $widget = $this->makeWidget('admin.acl', 'read');

        $acl = $this->createStub(AclInterface::class);
        $acl->method('isAllowed')->willReturn(false);

        $iterator = new AclWidgetFilterIterator(
            new ArrayIterator([$widget]),
            $acl,
            new GenericRole('Administrator'),
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
