<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Twig;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @phpstan-import-type Item from Pool
 */
final class GroupRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private Pool $pool,
    ) {
    }

    /**
     * NEXT_MAJOR: Remove the label_catalogue key.
     *
     * @phpstan-return array<array{
     *     label: string,
     *     label_catalogue?: string,
     *     translation_domain: string,
     *     icon: string,
     *     items: list<AdminInterface<object>>,
     *     keep_open: bool,
     *     on_top: bool,
     *     roles: list<string>,
     *     provider?: string,
     * }>
     */
    public function getDashboardGroupsWithCreatableAdmins(): array
    {
        $groups = [];

        foreach ($this->pool->getDashboardGroups() as $group) {
            foreach ($group['items'] as $admin) {
                if ($admin->hasRoute('create') && $admin->hasAccess('create')) {
                    $groups[] = $group;

                    continue 2;
                }
            }
        }

        return $groups;
    }
}
