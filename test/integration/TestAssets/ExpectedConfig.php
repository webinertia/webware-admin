<?php

declare(strict_types=1);

namespace WebwareTestIntegration\Admin\TestAssets;

use Webware\Admin\AdminInterface;
use Webware\Admin\Container\Configuration;
use Webware\Admin\Container\DashboardHandlerFactory;
use Webware\Admin\Container\DashboardMiddlewareFactory;
use Webware\Admin\Container\RouteProviderFactory;
use Webware\Admin\Middleware\DashboardMiddleware;
use Webware\Admin\RequestHandler\DashboardHandler;
use Webware\Admin\RouteProvider;
use Webware\Admin\View\Helper\AdminUrl;
use Webware\Admin\View\Helper\AdminUrlFactory;

use function dirname;

final class ExpectedConfig
{
    public static function getDashboardResource(): string
    {
        return Configuration::ADMIN_ROUTE_NAME_PREFIX_VALUE . 'dashboard.read';
    }

    /**
     * @return array{
     *   roles: array<string, list<string>>,
     *   resources: list<string>,
     *   allow: array<string, list<string>>
     * }
     */
    public static function getExpectedAclConfig(): array
    {
        $dashboard = self::getDashboardResource();

        return [
            'roles'     => [
                'User'          => [],
                'Administrator' => ['User'],
            ],
            'resources' => [
                $dashboard,
            ],
            'allow'     => [
                'Administrator' => [
                    $dashboard,
                ],
            ],
        ];
    }

    /**
     * @return array{
     *   dependencies: array{
     *     factories: array<class-string, class-string>
     *   },
     *   router: array{
     *     route-providers: list<class-string>
     *   },
     *   templates: array{
     *     paths: array{
     *       admin: list<string>
     *     }
     *   },
     *   view_helpers: array{
     *     aliases: array<string, class-string>,
     *     factories: array<class-string, class-string>
     *   },
     *   'mezzio-authorization-acl': array{
     *     roles: array<string, list<string>>,
     *     resources: list<string>,
     *     allow: array<string, list<string>>
     *   },
     *   Webware\Admin\AdminInterface: array{
     *     admin_route_segment: string,
     *     admin_route_name_prefix: string
     *   }
     * }
     */
    public static function getExpectedConfig(): array
    {
        return [
            'dependencies'             => self::getExpectedDependencies(),
            'router'                   => self::getExpectedRouteProviders(),
            'templates'                => self::getExpectedTemplates(),
            'view_helpers'             => self::getExpectedViewHelpers(),
            'mezzio-authorization-acl' => self::getExpectedAclConfig(),
            AdminInterface::class      => self::getExpectedDefaultConfig(),
        ];
    }

    /**
     * @return array{
     *   admin_route_segment: string,
     *   admin_route_name_prefix: string
     * }
     */
    public static function getExpectedDefaultConfig(): array
    {
        return [
            Configuration::ADMIN_ROUTE_SEGMENT_KEY     => Configuration::ADMIN_ROUTE_SEGMENT_VALUE,
            Configuration::ADMIN_ROUTE_NAME_PREFIX_KEY => Configuration::ADMIN_ROUTE_NAME_PREFIX_VALUE,
        ];
    }

    /**
     * @return array{
     *   factories: array<class-string, class-string>
     * }
     */
    public static function getExpectedDependencies(): array
    {
        return [
            'factories' => [
                DashboardHandler::class    => DashboardHandlerFactory::class,
                DashboardMiddleware::class => DashboardMiddlewareFactory::class,
                RouteProvider::class       => RouteProviderFactory::class,
            ],
        ];
    }

    /**
     * @return array{
     *   route-providers: list<class-string>
     * }
     */
    public static function getExpectedRouteProviders(): array
    {
        return [
            'route-providers' => [
                RouteProvider::class,
            ],
        ];
    }

    /**
     * @return array{
     *   paths: array{
     *     admin: list<string>
     *   }
     * }
     */
    public static function getExpectedTemplates(): array
    {
        return [
            'paths' => [
                'admin' => [
                    dirname(
                        path  : __DIR__,
                        levels: 3,
                    ) . '/templates/admin',
                ],
            ],
        ];
    }

    /**
     * @return array{
     *   aliases: array<string, class-string>,
     *   factories: array<class-string, class-string>
     * }
     */
    public static function getExpectedViewHelpers(): array
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
}
