<?php

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

final class SecurityExtension extends \Twig_Extension
{
    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @param AdminInterface $admin
     */
    public function setAdmin(AdminInterface $admin)
    {
        $this->admin = $admin;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('sonata_is_granted', array($this, 'isGranted')),
        );
    }

    public function isGranted($attributes, $object = null, AdminInterface $admin = null)
    {
        if ($admin === null) {
            $admin = $this->admin;
        }

        return $admin->getSecurityHandler()->isGranted($admin, $attributes, $object ?: $admin);
    }
}
