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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

interface AuditManagerInterface
{
    /**
      * @param $serviceId
      * @param array $classes
      */
    function setReader($serviceId, array $classes);

    /**
      * @param $class
      * @return bool
      */
    function hasReader($class);

     /**
      * @param $class
      * @return \Sonata\AdminBundle\Model\AuditReaderInterface
      * @throws \RuntimeException
      */
    function getReader($class);
}