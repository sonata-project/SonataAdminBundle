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

/**
 * Admin menu voter based on extra `admin`.
 *
 * @author Samusev Andrey <andrey.simfi@ya.ru>
 */
class AdminVoter implements VoterInterface
{
    /**
     * @var Request
     */
    private $request = null;

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function matchItem(ItemInterface $item)
    {
        $admin = $item->getExtra('admin');
        $match = null;
        if ($admin instanceof AdminInterface
            && $admin->hasRoute('list') && $admin->isGranted('LIST')
            && $this->request && $this->request->get('_sonata_admin') == $admin->getCode()
        ) {
            $match = true;
        }

        $route = $item->getExtra('route');
        if ($route && $this->request && $route == $this->request->get('_route')) {
            $match = true;
        }

        return $match;
    }
}
