<?php

declare(strict_types=1);

namespace Webware\Admin;

use FilterIterator;
use Iterator;
use Laminas\Permissions\Acl\AclInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Override;

/**
 * Wraps the WidgetContainer and accepts only those widgets the current
 * user's roles are permitted to see, according to the ACL.
 *
 * Fails closed: a null user denies every widget.
 *
 * @extends FilterIterator<int, WidgetInterface, Iterator<int, WidgetInterface>>
 */
final class AclWidgetFilterIterator extends FilterIterator
{
    public function __construct(
        WidgetContainer $widgets,
        private readonly AclInterface $acl,
        private readonly ?RoleInterface $user,
    ) {
        parent::__construct($widgets->getIterator());
    }

    #[Override]
    public function accept(): bool
    {
        if (null === $this->user) {
            return false;
        }

        /** @var WidgetInterface $widget */
        $widget = $this->current();

        return $this->acl->isAllowed($this->user, $widget->resourceId, $widget->privilege);
    }
}
