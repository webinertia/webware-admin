<?php

declare(strict_types=1);

namespace Webware\Admin\Container;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use Webware\Admin\RequestHandler\DashboardHandler;

final class DashboardHandlerFactory
{
    public function __invoke(ContainerInterface $container): DashboardHandler
    {
        return new DashboardHandler(
            template: $container->get(TemplateRendererInterface::class),
        );
    }
}
