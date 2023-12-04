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

namespace Sonata\AdminBundle\ArgumentResolver;

use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

// TODO: Remove this interface when dropping support of Symfony < 6.2 and replace its usage with ValueResolverInterface
if (interface_exists(ValueResolverInterface::class)) {
    /** @internal */
    interface CompatibleValueResolverInterface extends ValueResolverInterface
    {
    }
} else {
    /**
     * @internal
     *
     * @phpstan-ignore-next-line
     */
    interface CompatibleValueResolverInterface extends ArgumentValueResolverInterface
    {
    }
}
