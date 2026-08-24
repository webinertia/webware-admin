<?php

declare(strict_types=1);

namespace WebwareTest\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Admin\WidgetContainer;
use Webware\Admin\WidgetInterface;

use function array_map;
use function iterator_to_array;

#[CoversClass(WidgetContainer::class)]
final class WidgetContainerTest extends TestCase
{
    #[Test]
    public function addWidgetMaintainsAscendingOrder(): void
    {
        $container = new WidgetContainer();
        $container->addWidget($this->makeWidget(30));
        $container->addWidget($this->makeWidget(10));
        $container->addWidget($this->makeWidget(20));

        $widgets = iterator_to_array($container, preserve_keys: false);

        self::assertSame([10, 20, 30], array_map(static fn($w) => $w->order, $widgets));
    }

    #[Test]
    public function constructorWithoutWidgetYieldsEmptyIterator(): void
    {
        $container = new WidgetContainer();

        self::assertCount(0, iterator_to_array($container));
    }

    #[Test]
    public function constructorWithWidgetAddsItToTheContainer(): void
    {
        $container = new WidgetContainer($this->makeWidget(10));

        self::assertCount(1, iterator_to_array($container));
    }

    #[Test]
    public function getIteratorReturnsFreshIteratorPerCall(): void
    {
        $container = new WidgetContainer($this->makeWidget(1));

        $a = $container->getIterator();
        $b = $container->getIterator();

        self::assertNotSame($a, $b);
        self::assertCount(1, $a);
        self::assertCount(1, $b);
    }

    private function makeWidget(int $order, string $resourceId = 'admin.test'): WidgetInterface
    {
        return new class($order, $resourceId) implements WidgetInterface {
            public string $title {
                get => 'Test';
            }

            public string $template {
                get => 'test::widget';
            }

            public string $privilege {
                get => 'read';
            }

            public function __construct(
                public int $order,
                public string $resourceId,
            ) {}

            public function getResourceId(): string
            {
                return $this->resourceId;
            }
        };
    }
}
