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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdminValueResolver implements ArgumentValueResolverInterface
{
    /**
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

    public function __construct(AdminFetcherInterface $adminFetcher)
    {
        $this->adminFetcher = $adminFetcher;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!is_subclass_of($argument->getType(), AdminInterface::class)) {
            return false;
        }

        try {
            $admin = $this->adminFetcher->get($request);
        } catch (AdminCodeNotFoundException $exception) {
            return false;
        }

        return is_a($admin, $argument->getType());
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->adminFetcher->get($request);
    }
}
