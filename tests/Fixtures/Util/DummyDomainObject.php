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

use Symfony\Component\Security\Acl\Model\DomainObjectInterface;

final class DummyDomainObject implements DomainObjectInterface
{
    public function getObjectIdentifier(): string
    {
        return 'id';
    }
}
