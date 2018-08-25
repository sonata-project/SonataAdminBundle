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
final class InvalidModelManager extends InvalidArgumentException implements SonataException
{
    /**
     * @param string $manager
     * @param string $availableManagers
     *
     * @return self
     */
    public static function create($manager, $availableManagers)
    {
        return new self(sprintf(
            'Invalid manager type "%s". Available manager types are "%s".',
            $manager,
            $availableManagers
        ));
    }
}
