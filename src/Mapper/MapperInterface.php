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

namespace Sonata\AdminBundle\Mapper;

use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * This interface is used to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @method array keys()
 */
interface MapperInterface
{
    /**
     * @return AdminInterface
     */
    public function getAdmin();

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     *
     * @return static
     */
    public function remove($key);

    /**
     * NEXT_MAJOR: Uncomment this.
     *
     * Returns configured keys.
     *
     * @return string[]
     */
    //public function keys(): array;

    /**
     * @param array $keys field names
     *
     * @return static
     */
    public function reorder(array $keys);
}
