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
final class ExtensionNotFound extends InvalidArgumentException implements SonataException
{
    /**
     * @param string $serviceId
     *
     * @return self
     */
    public static function fromServiceId($serviceId)
    {
        return new self(sprintf(
            'Unable to find extension service for id %s',
            $serviceId
        ));
    }
}
