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

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Mathieu Petrini <mathieupetrini@gmail.com>
 */
interface AdminAclUserManagerInterface
{
    /**
     * Batch configure the ACLs for all objects handled by an Admin.
     *
     * @return iterable<UserInterface|string>
     */
    public function findUsers(): iterable;
}
