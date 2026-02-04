<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Fixtures\Util;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Util\ObjectAclManipulator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

final class DummyObjectAclManipulator extends ObjectAclManipulator
{
    /**
     * @phpstan-throws void
     */
    public function batchConfigureAcls(OutputInterface $output, AdminInterface $admin, ?UserSecurityIdentity $securityIdentity = null): void
    {
    }
}
