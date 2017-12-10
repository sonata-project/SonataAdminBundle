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

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface AuditManagerInterface
{
    /**
     * Set AuditReaderInterface service id for array of $classes.
     *
     * @param string $serviceId
     */
    public function setReader($serviceId, array $classes);

    /**
     * Returns true if $class has AuditReaderInterface.
     *
     * @param string $class
     *
     * @return bool
     */
    public function hasReader($class);

    /**
     * Get AuditReaderInterface service for $class.
     *
     * @param string $class
     *
     * @throws \RuntimeException
     *
     * @return AuditReaderInterface
     */
    public function getReader($class);
}
