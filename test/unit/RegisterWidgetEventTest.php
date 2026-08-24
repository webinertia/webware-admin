<?php

declare(strict_types=1);

namespace WebwareTest\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Admin\Event\RegisterWidgetEvent;
use Webware\Admin\WidgetContainer;
use Webware\Admin\WidgetInterface;

use function array_map;
use function iterator_to_array;

#[CoversClass(RegisterWidgetEvent::class)]
final class RegisterWidgetEventTest extends TestCase
{
    #[Test]
    public function getWidgetContainerReturnsEmptyWhenNoWidgetsRegistered(): void
    {
        $event = new RegisterWidgetEvent();

        self::assertCount(0, $event->getWidgetContainer());
    }

    #[Test]
    public function getWidgetContainerReturnsSameContainerInstance(): void
    {
        $event = new RegisterWidgetEvent();

        self::assertSame($event->getWidgetContainer(), $event->getWidgetContainer());
        self::assertInstanceOf(WidgetContainer::class, $event->getWidgetContainer());
    }

    #[Test]
    public function registerWidgetDelegatesToWidgetContainer(): void
    {
        $event = new RegisterWidgetEvent();
        $event->registerWidget($this->makeWidget(30));
        $event->registerWidget($this->makeWidget(10));
        $event->registerWidget($this->makeWidget(20));

        $widgets = iterator_to_array($event->getWidgetContainer(), preserve_keys: false);

        self::assertCount(3, $widgets);
        self::assertSame([10, 20, 30], array_map(static fn($w) => $w->order, $widgets));
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
