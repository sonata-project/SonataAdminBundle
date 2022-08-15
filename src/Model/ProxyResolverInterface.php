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

namespace Sonata\AdminBundle\Model;

interface ProxyResolverInterface
{
    /**
     * Gets the real class name of an object (even if its a proxy).
     *
     * @phpstan-return class-string
     */
    public function getRealClass(object $object): string;
}
