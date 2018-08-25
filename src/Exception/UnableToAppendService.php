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
final class UnableToAppendService extends RuntimeException implements SonataException
{
    /**
     * @param string $serviceId
     * @param string $file
     *
     * @return self
     */
    public static function forService($serviceId, $file)
    {
        return new self(sprintf(
            'Unable to append service "%s" to the file "%s". You will have to do it manually.',
            $serviceId,
            $file
        ));
    }
}
