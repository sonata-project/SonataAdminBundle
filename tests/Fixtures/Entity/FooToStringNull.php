<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Fixtures\Entity;

final class FooToStringNull
{
    /**
     * @psalm-suppress MethodSignatureMustProvideReturnType
     */
    public function __toString()
    {
        // In case __toString returns an attribute not yet set
        return null;
    }
}
