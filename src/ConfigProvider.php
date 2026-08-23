<?php

declare(strict_types=1);

namespace Webware\Admin;

use Webware\Admin\Container\DashboardHandlerFactory;
use Webware\Admin\Container\DashboardMiddlewareFactory;
use Webware\Admin\Container\RouteProviderFactory;
use Webware\Admin\Middleware\DashboardMiddleware;
use Webware\Admin\RequestHandler\DashboardHandler;
use Webware\Admin\View\Helper\AdminUrl;
use Webware\Admin\View\Helper\AdminUrlFactory;

/**
 * @type AclConfig = array{
 *   roles: array<string, list<string>>,
 *   resources: list<string>,
 *   allow: array<string, list<string>>
 * }
 * @type DefaultConfig = array{
 *   admin_route_segment: string,
 *   admin_route_name_prefix: string
 * }
 * @type Dependencies = array{
 *   factories: array<class-string, class-string>
 * }
 * @type RouteProviders = array{
 *   route-providers: list<class-string>
 * }
 * @type Templates = array{
 *   paths: array<string, list<string>>
 * }
 * @type ViewHelpers = array{
 *   aliases: array<string, class-string>,
 *   factories: array<class-string, class-string>
 * }
 * @type ProviderConfig = array{
 *   dependencies: Dependencies,
 *   router: RouteProviders,
 *   templates: Templates,
 *   view_helpers: ViewHelpers,
 *   'mezzio-authorization-acl': AclConfig,
 *   Webware\Admin\AdminInterface: DefaultConfig
 * }
 * @internal
 */
final readonly class ConfigProvider
{
    /**
     * Default authorization config for the admin UI routes, matching the
     * structure consumed by mezzio/mezzio-authorization-acl. Resources are the
     * route names registered by this package's RouteProvider. webware-acl does
     * not consume this config (it is database driven); host applications using
     * laminas-permissions-acl merge it with their own via the config aggregator.
     *
     * @return AclConfig
     */
    public function getAclConfig(): array
    {
        return [
            'roles'     => [
                'User'          => [],
                'Administrator' => ['User'],
            ],
            'resources' => [
                Container\Configuration::ADMIN_ROUTE_NAME_PREFIX_VALUE . 'dashboard.read',
            ],
            'allow'     => [
                'Administrator' => [
                    Container\Configuration::ADMIN_ROUTE_NAME_PREFIX_VALUE . 'dashboard.read',
                ],
            ],
        ];
    }

    /** @return DefaultConfig */
    public function getDefaultConfig(): array
    {
        return [
            Container\Configuration::ADMIN_ROUTE_SEGMENT_KEY     => Container\Configuration::ADMIN_ROUTE_SEGMENT_VALUE,
            Container\Configuration::ADMIN_ROUTE_NAME_PREFIX_KEY => Container\Configuration::ADMIN_ROUTE_NAME_PREFIX_VALUE,
        ];
    }

    /** @return Dependencies */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                DashboardHandler::class    => DashboardHandlerFactory::class,
                DashboardMiddleware::class => DashboardMiddlewareFactory::class,
                RouteProvider::class       => RouteProviderFactory::class,
            ],
        ];
    }

    /** @return RouteProviders */
    public function getRouteProviders(): array
    {
        return [
            'route-providers' => [
                RouteProvider::class,
            ],
        ];
    }

    /** @return Templates */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'admin' => [__DIR__ . '/../templates/admin'],
            ],
        ];
    }

    /** @return ViewHelpers */
    public function getViewHelpers(): array
    {
        return [
            'aliases'   => [
                'adminUrl' => AdminUrl::class,
            ],
            'factories' => [
                AdminUrl::class => AdminUrlFactory::class,
            ],
        ];
    }

    /** @return ProviderConfig */
    public function __invoke(): array
    {
        return [
            'dependencies'             => $this->getDependencies(),
            'router'                   => $this->getRouteProviders(),
            'templates'                => $this->getTemplates(),
            'view_helpers'             => $this->getViewHelpers(),
            'mezzio-authorization-acl' => $this->getAclConfig(),
            AdminInterface::class      => $this->getDefaultConfig(),
        ];
    }
}
