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
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Sonata\AdminBundle\Admin\AdminInterface;

class AclSecurityHandler implements SecurityHandlerInterface
{
    protected $securityContext;

    protected $superAdminRoles;

    /**
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param array $superAdminRoles
     */
    public function __construct(SecurityContextInterface $securityContext, array $superAdminRoles)
    {
        $this->securityContext = $securityContext;
        $this->superAdminRoles = $superAdminRoles;
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        if ($object instanceof AdminInterface) {
            foreach ($attributes as $pos => $attribute) {
                $attributes[$pos] = sprintf('ROLE_%s_%s',
                    str_replace('.', '_', strtoupper($admin->getCode())),
                    $attribute
                );
            }
        }

        $attributes = array_merge($attributes, $this->superAdminRoles);

        try {
            return $this->securityContext->isGranted($attributes, $object);
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
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