<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter\Persister;

/**
 * Filter persister are components responsible for storing filters for given admin.
 * So filters are not lost when you navigate.
 *
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface FilterPersisterInterface
{
    /**
     * Get persisted filters for given admin.
     *
     * @param string $admin The admin code
     *
     * @return array The persisted filters
     */
    public function get($admin);

    /**
     * Set persisted filters for given admin.
     *
     * @param string $admin   The admin code
     * @param array  $filters The filters to persist
     */
    public function set($admin, $filters);

    /**
     * Reset persisted filters for given admin.
     *
     * @param string $admin The admin code
     */
    public function reset($admin);
}
