<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\ArgumentResolver;

use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;

// TODO: Remove this interface when dropping support of Symfony < 6.2 and replace its usage with ValueResolverInterface
if (interface_exists(ValueResolverInterface::class)) {
    /** @internal */
    interface CompatibleValueResolverInterface extends ValueResolverInterface
    {
    }
} elseif (interface_exists(ArgumentValueResolverInterface::class)) {
    /** @internal */
    interface CompatibleValueResolverInterface extends ArgumentValueResolverInterface
    {
    }
}
