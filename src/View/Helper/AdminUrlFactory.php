<?php

declare(strict_types=1);

namespace Webware\Admin\View\Helper;

use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webware\Admin\Container\Configuration;

final readonly class AdminUrlFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdminUrl
    {
        return new AdminUrl(
            urlHelper      : $container->get(UrlHelper::class),
            routeNamePrefix: Configuration::getAdminRouteNamePrefix($container, self::class),
        );
    }
}
