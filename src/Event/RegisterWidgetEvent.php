<?php

declare(strict_types=1);

namespace Webware\Admin\Event;

use Webware\Admin\WidgetContainer;
use Webware\Admin\WidgetInterface;

/**
 * Mutable collect event dispatched by DashboardMiddleware.
 *
 * Modules register PSR-14 listeners for this event and call registerWidget()
 * to contribute their widget to the admin dashboard.
 */
final class RegisterWidgetEvent
{
    public function __construct(
        private readonly WidgetContainer $widgets = new WidgetContainer(),
    ) {}

    public function getWidgetContainer(): WidgetContainer
    {
        return $this->widgets;
    }

    public function registerWidget(WidgetInterface $widget): void
    {
        $this->widgets->addWidget($widget);
    }
}
