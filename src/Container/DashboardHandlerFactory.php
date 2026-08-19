<?php

declare(strict_types=1);

namespace Webware\Admin\Container;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webware\Admin\RequestHandler\DashboardHandler;

final class DashboardHandlerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DashboardHandler
    {
        return new DashboardHandler(
            template: $container->get(TemplateRendererInterface::class),
        );
    }
}
