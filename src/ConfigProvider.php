<?php

declare(strict_types=1);

namespace Webware\Admin;

use Webware\Admin\Container\DashboardHandlerFactory;
use Webware\Admin\Container\DashboardMiddlewareFactory;
use Webware\Admin\Container\RouteProviderFactory;
use Webware\Admin\Http\Middleware\DashboardMiddleware;
use Webware\Admin\Http\RequestHandler\DashboardHandler;
use Webware\Admin\View\Helper\AdminUrl;
use Webware\Admin\View\Helper\AdminUrlFactory;
use Webware\Core\AclInterface as CoreAclInterface;

use function dirname;

/**
 * @type AclConfig = array{
 *   roles: array<string, list<string>>,
 *   resources: list<string>,
 *   allow: array<string, list<string>>
 * }
 * @type DefaultConfig = array{
 *   admin_name: string
 * }
 * @type Dependencies = array{
 *   factories: array<class-string, class-string>,
 *   invokables: array<class-string, class-string>
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
 *   Webware\Core\AclInterface: array{rule_seed_providers: list<class-string>},
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
                Container\Configuration::ADMIN_NAME . '.dashboard.read',
            ],
            'allow'     => [
                'Administrator' => [
                    Container\Configuration::ADMIN_NAME . '.dashboard.read',
                ],
            ],
        ];
    }

    /** @return DefaultConfig */
    public function getDefaultConfig(): array
    {
        return [
            Container\Configuration::ADMIN_NAME_KEY => Container\Configuration::ADMIN_NAME,
        ];
    }

    /** @return Dependencies */
    public function getDependencies(): array
    {
        return [
            'factories'  => [
                DashboardHandler::class    => DashboardHandlerFactory::class,
                DashboardMiddleware::class => DashboardMiddlewareFactory::class,
                RouteProvider::class       => RouteProviderFactory::class,
            ],
            'invokables' => [
                Acl\RuleSeeds::class => Acl\RuleSeeds::class,
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
                'admin' => [dirname(__DIR__) . '/templates/default/admin'],
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
            CoreAclInterface::class    => ['rule_seed_providers' => [Acl\RuleSeeds::class]],
            AdminInterface::class      => $this->getDefaultConfig(),
        ];
    }
}
