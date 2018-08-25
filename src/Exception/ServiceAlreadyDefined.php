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
final class ServiceAlreadyDefined extends RuntimeException implements SonataException
{
    /**
     * @param string $serviceId
     * @param string $filePath
     *
     * @return self
     */
    public static function forService($serviceId, $filePath)
    {
        return new self(sprintf(
            'The service "%s" is already defined in the file "%s".',
            $serviceId,
            $filePath
        ));
    }
}
