<?php

declare(strict_types=1);

namespace Webware\Admin\View\Helper;

use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webware\Admin\Container\Configuration;
use Webware\Core\Exception;

final readonly class AdminUrlFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception\ExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdminUrl
    {
        return new AdminUrl(
            urlHelper      : $container->get(UrlHelper::class),
            routeNamePrefix: Configuration::getAdminNamePrefix($container, self::class),
        );
    }
}
