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

use InvalidArgumentException;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ServiceContainsInvalidCharacters extends InvalidArgumentException implements SonataException
{
    /**
     * @param string $serviceId
     * @param string $characters
     *
     * @return self
     */
    public static function create($serviceId, $characters)
    {
        return new self(sprintf(
            'Service ID "%s" contains invalid character "%s".',
            $serviceId,
            $characters
        ));
    }
}
