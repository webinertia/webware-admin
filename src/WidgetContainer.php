<?php

declare(strict_types=1);

namespace Webware\Admin;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Override;

use function usort;

/**
 * @implements IteratorAggregate<int, WidgetInterface>
 */
final class WidgetContainer implements IteratorAggregate
{
    /** @var list<WidgetInterface> */
    private array $widgets = [];

    public function __construct(?WidgetInterface $widget = null)
    {
        if (null !== $widget) {
            $this->addWidget($widget);
        }
    }

    public function addWidget(WidgetInterface $widget): void
    {
        $this->widgets[] = $widget;
        usort(
            $this->widgets,
            static fn(WidgetInterface $a, WidgetInterface $b): int => $a->order <=> $b->order,
        );
    }

    /**
     * @return Iterator<int, WidgetInterface>
     */
    #[Override]
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->widgets);
    }
}
