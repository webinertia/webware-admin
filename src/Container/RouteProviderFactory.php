<?php

declare(strict_types=1);

namespace Webware\Admin\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webware\Admin\RouteProvider;
use Webware\Core\Exception;

final readonly class RouteProviderFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception\ExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RouteProvider
    {
        $adminRouteSegment    = Configuration::getAdminSegment($container, self::class);
        $adminRouteNamePrefix = Configuration::getAdminNamePrefix($container, self::class);

        // This component's own routes are the admin namespace itself: segment 'admin',
        // name prefix 'admin.'. Both resolve through getAdminName() so an application that
        // relocates the namespace moves these routes with it.
        return new RouteProvider(
            $adminRouteSegment,
            $adminRouteNamePrefix,
        );
    }
}
