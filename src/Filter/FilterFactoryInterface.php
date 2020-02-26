<?php

declare(strict_types=1);

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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface FilterFactoryInterface
{
    /**
     * @param string $name
     * @param string $type
     *
     * @return FilterInterface
     */
    public function create($name, $type, array $options = []);
}
