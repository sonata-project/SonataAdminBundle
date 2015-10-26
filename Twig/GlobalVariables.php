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

/**
 * Class GlobalVariables.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Pool
     */
    public function getAdminPool()
    {
        return $this->container->get('sonata.admin.pool');
    }

    /**
     * @param string $code
     * @param string $action
     * @param array  $parameters
     * @param mixed  $absolute
     *
     * @return string
     */
    public function url($code, $action, $parameters = array(), $absolute = false)
    {
        list($action, $code) = $this->getCodeAction($code, $action);

        return $this->getAdminPool()->getAdminByAdminCode($code)->generateUrl($action, $parameters, $absolute);
    }

    /**
     * @param string $code
     * @param string $action
     * @param mixed  $object
     * @param array  $parameters
     * @param mixed  $absolute
     *
     * @return string
     */
    public function objectUrl($code, $action, $object, $parameters = array(), $absolute = false)
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
        if ($pipe = strpos('|', $code)) {
            // convert code=sonata.page.admin.page|sonata.page.admin.snapshot, action=list
            // to => sonata.page.admin.page|sonata.page.admin.snapshot.list
            $action = $code.'.'.$action;
            $code = substr($code, 0, $pipe);
        }

        return array($action, $code);
    }
}
