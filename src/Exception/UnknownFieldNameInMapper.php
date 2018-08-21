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
final class UnknownFieldNameInMapper extends RuntimeException implements SonataException
{
    /**
     * @param string $mapper
     *
     * @return self
     */
    public static function create($mapper)
    {
        return new self(sprintf(
            'Unknown field name in %s mapper. '
            .'Field name should be either of FieldDescriptionInterface interface or string.',
            $mapper
        ));
    }
}
