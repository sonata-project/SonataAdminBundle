<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Model;

interface AuditManagerInterface
{
    /**
     * @param string $serviceId
     * @param array  $classes
     */
    public function setReader($serviceId, array $classes);

    /**
     * @param string $class
     *
     * @return bool
     */
    public function hasReader($class);

    /**
     * @param string $class
     *
     * @return \Sonata\AdminBundle\Model\AuditReaderInterface
     * @throws \RuntimeException
     */
    public function getReader($class);
}
