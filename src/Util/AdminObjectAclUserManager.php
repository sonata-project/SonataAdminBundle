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

namespace Sonata\AdminBundle\Util;

/**
 * @author Mathieu Petrini <mathieupetrini@gmail.com>
 */
interface AdminObjectAclUserManager
{
    /**
     * Batch configure the ACLs for all objects handled by an Admin.
     */
    public function findUsers(): \Traversable;
}
