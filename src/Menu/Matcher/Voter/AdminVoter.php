<?php

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
class AdminVoter implements VoterInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Request
     */
    private $request = null;

    public function __construct(RequestStack $requestStack = null)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @deprecated since version 3.31. Pass a RequestStack to the constructor instead.
     *
     * @return $this
     */
    public function setRequest($request)
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated since version 3.31.
                Pass a Symfony\Component\HttpFoundation\RequestStack
                in the constructor instead.',
            __METHOD__),
            E_USER_DEPRECATED
        );

        $this->request = $request;

        return $this;
    }

    public function matchItem(ItemInterface $item)
    {
        $admin = $item->getExtra('admin');

        $request = $this->request;
        if (null !== $this->requestStack) {
            $request = $this->requestStack->getMasterRequest();
        }

        if ($admin instanceof AdminInterface
            && $admin->hasRoute('list') && $admin->hasAccess('list')
            && $request
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
        if ($route && $request && $route == $request->get('_route')) {
            return true;
        }

        return null;
    }
}
