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

namespace Sonata\AdminBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\AdminCodeNotFoundException;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @deprecated since sonata-project/admin-bundle 3.105.
 *
 * NEXT_MAJOR: Remove this class.
 */
final class AdminParamConverter implements ParamConverterInterface
{
    /**
     * @var AdminFetcherInterface
     */
    private $adminFetcher;

    public function __construct(AdminFetcherInterface $adminFetcher)
    {
        $this->adminFetcher = $adminFetcher;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $admin = $this->adminFetcher->get($request);
        } catch (AdminCodeNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }

        if (!is_a($admin, $configuration->getClass())) {
            throw new \LogicException(sprintf(
                '"%s" MUST be an instance of "%s", "%s" given.',
                $configuration->getName(),
                $configuration->getClass(),
                \get_class($admin)
            ));
        }

        $request->attributes->set($configuration->getName(), $admin);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return is_subclass_of($configuration->getClass(), AdminInterface::class);
    }
}
