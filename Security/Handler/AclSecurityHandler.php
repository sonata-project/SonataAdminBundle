<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Security\Handler;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class AclSecurityHandler implements SecurityHandlerInterface
{
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted($attributes, $object = null)
    {
        return $this->securityContext->isGranted($attributes, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
        $baseRole = 'ROLE_'.str_replace('.', '_', strtoupper($admin->getCode())).'_%s';

        $results = array();
        foreach ($admin->getSecurityInformation() as $name => $permissions) {
            $results[sprintf($baseRole, $name)] = $permissions;
        }

        return $results;
    }
}