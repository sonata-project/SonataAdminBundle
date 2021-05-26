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

namespace Sonata\AdminBundle\Admin;

/**
 * @author Jules Lamur <contact@juleslamur.fr>
 */
interface AdminTreeInterface
{
    /**
     * Returns the root ancestor or itself if not a child.
     *
     * @return AdminInterface<object>
     */
    public function getRootAncestor(): AdminInterface;

    /**
     * Returns the depth of the admin.
     * e.g. 0 if not a child; 2 if child of a child; etc...
     */
    public function getChildDepth(): int;

    /**
     * Returns the current leaf child admin instance,
     * or null if there's no current child.
     *
     * @return AdminInterface<object>|null
     */
    public function getCurrentLeafChildAdmin(): ?AdminInterface;
}
