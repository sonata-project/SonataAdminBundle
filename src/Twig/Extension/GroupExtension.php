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

namespace Sonata\AdminBundle\Twig\Extension;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class GroupExtension extends AbstractExtension
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_sonata_dashboard_groups_with_creatable_admins', [$this, 'getDashboardGroupsWithCreatableAdmins']),
        ];
    }

    /**
     * @phpstan-return array<array{
     *  label: string,
     *  label_catalogue: string,
     *  icon: string,
     *  item_adds: array,
     *  items: array<AdminInterface<object>>,
     *  keep_open: bool,
     *  on_top: bool,
     *  roles: list<string>
     * }>
     */
    public function getDashboardGroupsWithCreatableAdmins(): array
    {
        $groups = [];

        foreach ($this->pool->getDashboardGroups() as $group) {
            $filteredGroups = array_filter($group['items'], static function (AdminInterface $admin): bool {
                return $admin->hasRoute('create') && $admin->hasAccess('create');
            });

            if (\count($filteredGroups) > 0) {
                $groups[] = $group;
            }
        }

        return $groups;
    }
}
