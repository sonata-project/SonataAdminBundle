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

namespace Sonata\AdminBundle\Tests\Fixtures\Util;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Util\ObjectAclManipulator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

class DummyObjectAclManipulator extends ObjectAclManipulator
{
    public function batchConfigureAcls(OutputInterface $output, AdminInterface $admin, UserSecurityIdentity $securityIdentity = null): void
    {
    }
}
