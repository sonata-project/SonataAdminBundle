<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter;

/**
 * Interface FilterFactoryInterface.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FilterFactoryInterface
{
    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     *
     * @return mixed
     */
    public function create($name, $type, array $options = array());
}
