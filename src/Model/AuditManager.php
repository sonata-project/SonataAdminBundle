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

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AuditManager implements AuditManagerInterface
{
    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @var array
     */
    protected $readers = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setReader($serviceId, array $classes)
    {
        $this->readers[$serviceId] = $classes;
    }

    public function hasReader($class)
    {
        foreach ($this->readers as $classes) {
            if (in_array($class, $classes)) {
                return true;
            }
        }

        return false;
    }

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
