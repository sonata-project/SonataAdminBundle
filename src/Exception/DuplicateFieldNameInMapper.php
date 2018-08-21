<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Exception;

use RuntimeException;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class DuplicateFieldNameInMapper extends RuntimeException implements SonataException
{
    /**
     * @param string $field
     * @param string $mapper
     *
     * @return self
     */
    public static function create($field, $mapper)
    {
        return new self(sprintf(
            'Duplicate field name "%s" in %s mapper. Names should be unique.',
            $field,
            $mapper
        ));
    }
}
