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
final class ExportFormatNotAllowed extends RuntimeException implements SonataException
{
    /**
     * @param string $format
     * @param string $class
     * @param string $allowedFormats
     *
     * @return self
     */
    public static function create($format, $class, $allowedFormats)
    {
        return new self(sprintf(
            'Export in format `%s` is not allowed for class: `%s`. Allowed formats are: `%s`',
            $format,
            $class,
            $allowedFormats
        ));
    }
}
