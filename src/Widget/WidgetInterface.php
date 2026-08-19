<?php

declare(strict_types=1);

namespace Webware\Admin\Widget;

use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Contract for admin dashboard widgets.
 *
 * Implementing classes must provide a get hook for each property.
 * The resource and privilege are used by AclWidgetFilterIterator to
 * determine whether the current user may see this widget.
 */
interface WidgetInterface extends ResourceInterface
{
    /** Display title shown in the widget header. */
    public string $title { get; }

    /** ACL resource identifier required to view this widget. */
    public string $resourceId { get; }

    /** ACL privilege required to view this widget. */
    public string $privilege { get; }

    /**
     * Namespaced template string passed to partial(), e.g. 'product::admin-widget'.
     * Each module supplies its own partial template.
     */
    public string $template { get; }

    /** Render order — lower values appear first. */
    public int $order { get; }
}
