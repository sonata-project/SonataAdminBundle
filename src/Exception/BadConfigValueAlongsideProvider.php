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
final class BadConfigValueAlongsideProvider extends InvalidArgumentException implements SonataException
{
    /**
     * @param string $configuration
     *
     * @return self
     */
    public static function fromConfiguration($configuration)
    {
        return new self(sprintf(
            'The config value "%s" cannot be used alongside "provider" config value',
            $configuration
        ));
    }

    /**
     * @return self
     */
    public static function badParameterForItems()
    {
        return new self('Expected either parameters "route" and "label" for array items');
    }
}
