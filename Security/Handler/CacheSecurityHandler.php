<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Security\Handler;

use Sonata\AdminBundle\Admin\AdminInterface;

final class CacheSecurityHandler implements SecurityHandlerInterface
{
    /**
     * @var SecurityHandlerInterface
     */
    private $handler;

    /**
     * @var array
     */
    private $cache;

    /**
     * @param SecurityHandlerInterface $handler
     */
    public function __construct(SecurityHandlerInterface $handler)
    {
        $this->handler = $handler;
        $this->cache = array();
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        $key = md5(json_encode($attributes)).'/'.spl_object_hash($object);

        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->handler->isGranted($admin, $attributes, $object);
        }

        return $this->cache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRole(AdminInterface $admin)
    {
        return $this->handler->getBaseRole($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function buildSecurityInformation(AdminInterface $admin)
    {
        return $this->handler->buildSecurityInformation($admin);
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectSecurity(AdminInterface $admin, $object)
    {
        $this->handler->createObjectSecurity($admin, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectSecurity(AdminInterface $admin, $object)
    {
        $this->handler->deleteObjectSecurity($admin, $object);
    }
}
