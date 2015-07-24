<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AuditManager implements AuditManagerInterface
{
    protected $classes = array();

    protected $readers = array();

    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function setReader($serviceId, array $classes)
    {
        $this->readers[$serviceId] = $classes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasReader($class)
    {
        foreach ($this->readers as $classes) {
            if (in_array($class, $classes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getReader($class)
    {
        foreach ($this->readers as $readerId => $classes) {
            if (in_array($class, $classes)) {
                return $this->container->get($readerId);
            }
        }

        throw new \RuntimeException(sprintf('The class "%s" does not have any reader manager', $class));
    }
}
