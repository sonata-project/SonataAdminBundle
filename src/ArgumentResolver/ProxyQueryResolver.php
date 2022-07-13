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

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ProxyQueryResolver implements ArgumentValueResolverInterface
{
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
     * @return iterable<ProxyQueryInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        foreach ($request->attributes as $attribute) {
            if ($attribute instanceof ProxyQueryInterface) {
                yield $attribute;

                break;
            }
        }
    }
}
