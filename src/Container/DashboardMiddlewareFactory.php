<?php

declare(strict_types=1);

namespace Webware\Admin\Container;

use Laminas\Permissions\Acl\AclInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Webware\Admin\Middleware\DashboardMiddleware;

final class DashboardMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DashboardMiddleware
    {
        return new DashboardMiddleware(
            dispatcher: $container->get(EventDispatcherInterface::class),
            acl       : $container->get(AclInterface::class),
        );
    }
}
