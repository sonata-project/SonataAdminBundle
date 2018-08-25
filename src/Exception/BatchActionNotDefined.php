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
final class BatchActionNotDefined extends RuntimeException implements SonataException
{
    /**
     * @param string $action
     *
     * @return self
     */
    public static function create($action)
    {
        return new self(sprintf(
            'The `%s` batch action is not defined',
            $action
        ));
    }
}
