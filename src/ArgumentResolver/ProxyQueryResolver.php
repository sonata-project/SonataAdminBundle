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

use SensioLabs\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ProxyQueryResolver implements CompatibleValueResolverInterface
{
    // TODO: Deprecate this method when dropping support of Symfony < 6.2
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();

        if (null === $type) {
            return false;
        }

        if (ProxyQueryInterface::class !== $type && !is_subclass_of($type, ProxyQueryInterface::class)) {
            return false;
        }

        foreach ($request->attributes as $attribute) {
            if ($attribute instanceof ProxyQueryInterface) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return iterable<ProxyQueryInterface<object>>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (null === $type) {
            return [];
        }

        if (ProxyQueryInterface::class !== $type && !is_subclass_of($type, ProxyQueryInterface::class)) {
            return [];
        }

        foreach ($request->attributes as $attribute) {
            if ($attribute instanceof ProxyQueryInterface) {
                return [$attribute];
            }
        }

        return [];
    }
}
