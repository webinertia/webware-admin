<?php

declare(strict_types=1);

namespace Webware\Admin\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Webware\Admin\AdminInterface;
use Webware\Core\Configuration as Config;

use function is_string;

/**
 * Route naming for the admin namespace.
 *
 * This component owns the admin base every other module nests under. It is resolved here
 * once — ADMIN_NAME by default, overridable through ADMIN_NAME_KEY — and consulted by any
 * component that publishes admin routes, so the fleet cannot disagree about the base.
 *
 * This component's own routes are that namespace itself: segment `admin`, name prefix
 * `admin.`.
 */
final readonly class Configuration extends Config
{
    public const string CONFIG_KEY = AdminInterface::class;

    /** This component's own name. */
    public const string COMPONENT_NAME = 'admin';

    /** Default admin namespace base. */
    public const string ADMIN_NAME = 'admin';

    /** Config key holding an override for ADMIN_NAME. */
    public const string ADMIN_NAME_KEY = 'admin_name';

    /**
     * The admin namespace base, e.g. `admin`. Consult this — never a local constant —
     * when composing an admin route name or segment, so an application that relocates
     * the namespace relocates every module's admin routes with it.
     *
     * @throws ContainerExceptionInterface
     */
    public static function getAdminName(ContainerInterface $container, string $callingFactory): string
    {
        $config = self::getConfig($container, $callingFactory);

        /** @var mixed $value */
        $value = $config[self::ADMIN_NAME_KEY] ?? null;

        if (! is_string($value) || '' === $value) {
            return self::ADMIN_NAME;
        }

        return $value;
    }

    /**
     * Prefix shared by this component's own route names, e.g. `admin.`.
     *
     * @throws ContainerExceptionInterface
     */
    public static function getAdminNamePrefix(ContainerInterface $container, string $callingFactory): string
    {
        return self::getAdminName($container, $callingFactory) . '.';
    }

    /**
     * URI segment this component's own routes are mounted on, e.g. `admin`.
     *
     * @throws ContainerExceptionInterface
     */
    public static function getAdminSegment(ContainerInterface $container, string $callingFactory): string
    {
        return self::getAdminName($container, $callingFactory);
    }
}
