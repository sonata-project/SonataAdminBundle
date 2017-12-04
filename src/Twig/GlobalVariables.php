<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Twig;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
{
    /**
     * @var ContainerInterface
     *
     * @deprecated Since version 3.5, will be removed in 4.0.
     * NEXT_MAJOR : remove this property
     */
    protected $container;

    /**
     * @var Pool
     */
    protected $adminPool;

    /**
     * @param ContainerInterface|Pool $adminPool
     */
    public function __construct($adminPool)
    {
        // NEXT_MAJOR : remove this block and set adminPool from parameter.
        if ($adminPool instanceof ContainerInterface) {
            @trigger_error(
                'Using an instance of Symfony\Component\DependencyInjection\ContainerInterface is deprecated since 
                version 3.5 and will be removed in 4.0. Use Sonata\AdminBundle\Admin\Pool instead.',
                E_USER_DEPRECATED
            );

            $this->adminPool = $adminPool->get('sonata.admin.pool');
        } elseif ($adminPool instanceof Pool) {
            $this->adminPool = $adminPool;
        } else {
            throw new \InvalidArgumentException(
                '$adminPool should be an instance of Sonata\AdminBundle\Admin\Pool'
            );
        }
    }

    /**
     * @return Pool
     */
    public function getAdminPool()
    {
        return $this->adminPool;
    }

    /**
     * @param string $code
     * @param string $action
     * @param array  $parameters
     * @param int    $absolute
     *
     * @return string
     */
    public function url($code, $action, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        list($action, $code) = $this->getCodeAction($code, $action);

        return $this->getAdminPool()->getAdminByAdminCode($code)->generateUrl($action, $parameters, $absolute);
    }

    /**
     * @param string $code
     * @param string $action
     * @param mixed  $object
     * @param array  $parameters
     * @param int    $absolute
     *
     * @return string
     */
    public function objectUrl($code, $action, $object, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        list($action, $code) = $this->getCodeAction($code, $action);

        return $this->getAdminPool()->getAdminByAdminCode($code)->generateObjectUrl($action, $object, $parameters, $absolute);
    }

    /**
     * @param $code
     * @param $action
     *
     * @return array
     */
    private function getCodeAction($code, $action)
    {
        if ($pipe = strpos($code, '|')) {
            // convert code=sonata.page.admin.page|sonata.page.admin.snapshot, action=list
            // to => sonata.page.admin.page|sonata.page.admin.snapshot.list
            $action = $code.'.'.$action;
            $code = substr($code, 0, $pipe);
        }

        return [$action, $code];
    }
}
