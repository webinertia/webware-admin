<?php

declare(strict_types=1);

namespace Webware\Admin\Acl;

use Override;
use Webware\Core\Acl\RuleSeed;
use Webware\Core\Acl\RuleSeedProviderInterface;
use Webware\Core\Acl\RuleType;
use Webware\Core\Role;

/**
 * The rule webware-admin itself owns: the dashboard, granted to Administrator.
 *
 * Without it the dashboard route denies every role, so its navigation link is hidden from everyone.
 * Developer reaches it through role inheritance from Administrator.
 *
 * @internal
 */
final readonly class RuleSeeds implements RuleSeedProviderInterface
{
    /**
     * @return list<RuleSeed>
     */
    #[Override]
    public function ruleSeeds(string $adminName): array
    {
        return [
            new RuleSeed(
                type      : RuleType::Allow,
                roleId    : Role::Administrator->value,
                resourceId: "{$adminName}.dashboard.read",
            ),
        ];
    }
}
