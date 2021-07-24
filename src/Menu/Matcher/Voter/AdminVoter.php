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

namespace Sonata\AdminBundle\Menu\Matcher\Voter;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Admin menu voter based on extra `admin`.
 *
 * @author Samusev Andrey <andrey.simfi@ya.ru>
 */
final class AdminVoter implements VoterInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function matchItem(ItemInterface $item): ?bool
    {
        $admin = $item->getExtra('admin');

        // TODO: Use $this->requestStack->getMainRequest() when dropping support of Symfony < 5.3
        $request = $this->getMainRequest();

        if ($admin instanceof AdminInterface
            && $admin->hasRoute('list') && $admin->hasAccess('list')
            && null !== $request
        ) {
            $requestCode = $request->get('_sonata_admin');

            if ($admin->getCode() === $requestCode) {
                return true;
            }

            foreach ($admin->getChildren() as $child) {
                if ($child->getBaseCodeRoute() === $requestCode) {
                    return true;
                }
            }
        }

        $route = $item->getExtra('route');
        if (null !== $route && null !== $request && $route === $request->get('_route')) {
            return true;
        }

        return null;
    }

    /**
     * TODO: Remove it when dropping support of Symfony < 5.3.
     *
     * @psalm-suppress DeprecatedMethod
     */
    private function getMainRequest(): ?Request
    {
        // @phpstan-ignore-next-line
        if (method_exists($this->requestStack, 'getMainRequest')) {
            return $this->requestStack->getMainRequest();   // symfony 5.3+
        }

        return $this->requestStack->getMasterRequest();
    }
}
