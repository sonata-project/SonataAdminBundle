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
     * @return array<string, int|string|array{type: int, value: mixed}>
     */
    public function get(string $adminCode): array;

    /**
     * Set persisted filters for given admin. Structure of filters:
     *     {string filter field} => array(
     *     "type" => {int filter type},
     *     "value" => {mixed filter value},
     *     ),
     *     ...,
     *     "_page" => {int page num},
     *     "_sort_by" => {string sort property},
     *     "_sort_order" => {string sort order (ASC|DESC)},
     *     "_per_page" => {int count rows per page}.
     *
     * @param array<string, int|string|array{type: int, value: mixed}> $filters
     */
    public function set(string $adminCode, array $filters): void;

    /**
     * Reset persisted filters for given admin.
     */
    public function reset(string $adminCode): void;
}
