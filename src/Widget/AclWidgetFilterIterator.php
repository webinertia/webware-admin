<?php

declare(strict_types=1);

namespace Webware\Admin\Widget;

use FilterIterator;
use Iterator;
use Laminas\Permissions\Acl\AclInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Override;

/**
 * Wraps an iterator of WidgetInterface instances and accepts only those
 * the current user's roles are permitted to see, according to the ACL.
 *
 * Fails closed: a null user denies every widget.
 *
 * @extends FilterIterator<int, WidgetInterface, Iterator<int, WidgetInterface>>
 */
final class AclWidgetFilterIterator extends FilterIterator
{
    /**
     * @param Iterator<int, WidgetInterface> $iterator
     */
    public function __construct(
        Iterator $iterator,
        private readonly AclInterface $acl,
        private readonly ?RoleInterface $user,
    ) {
        parent::__construct($iterator);
    }

    #[Override]
    public function accept(): bool
    {
        $widget = $this->current();

        if (! $widget instanceof WidgetInterface || null === $this->user) {
            return false;
        }

        return $this->acl->isAllowed($this->user, $widget->resourceId, $widget->privilege);
    }
}
