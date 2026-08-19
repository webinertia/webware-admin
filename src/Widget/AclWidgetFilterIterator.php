<?php

declare(strict_types=1);

namespace Webware\Admin\Widget;

use FilterIterator;
use Iterator;
use Override;
use Webware\Acl\AclInterface;
use Webware\UserManager\UserInterface;

/**
 * Wraps an iterator of WidgetInterface instances and accepts only those
 * the current user's roles are permitted to see, according to the ACL.
 *
 * @extends FilterIterator<int, WidgetInterface, Iterator<int, WidgetInterface>>
 */
final class AclWidgetFilterIterator extends FilterIterator
{
    /**
     * @param Iterator $iterator
     */
    public function __construct(
        Iterator $iterator,
        private readonly AclInterface $acl,
        private readonly ?UserInterface $user,
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
