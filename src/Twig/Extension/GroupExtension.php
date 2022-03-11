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

use Sonata\AdminBundle\Twig\GroupRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @phpstan-import-type Item from \Sonata\AdminBundle\Admin\Pool
 */
final class GroupExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_sonata_dashboard_groups_with_creatable_admins', [GroupRuntime::class, 'getDashboardGroupsWithCreatableAdmins']),
        ];
    }
}
