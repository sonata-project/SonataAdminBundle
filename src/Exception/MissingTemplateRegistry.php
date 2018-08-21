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
final class MissingTemplateRegistry extends RuntimeException implements SonataException
{
    /**
     * @param string $admin
     *
     * @return self
     */
    public static function forAdmin($admin)
    {
        return new self(sprintf(
            'Unable to find the template registry related to the current admin (%s)',
            $admin
        ));
    }
}
