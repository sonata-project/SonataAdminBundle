<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Route;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class DefaultRouteGenerator.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DefaultRouteGenerator implements RouteGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RoutesCache
     */
    private $cache;

    /**
     * @var array
     */
    private $caches = array();

    /**
     * @var string[]
     */
    private $loaded = array();

    /**
     * @param RouterInterface $router
     * @param RoutesCache     $cache
     */
    public function __construct(RouterInterface $router, RoutesCache $cache)
    {
        $this->router = $router;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($name, $parameters, $absolute);
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $arrayRoute = $this->generateMenuUrl($admin, $name, $parameters, $absolute);

        return $this->router->generate($arrayRoute['route'], $arrayRoute['routeParameters'], $arrayRoute['routeAbsolute']);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMenuUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        // if the admin is a child we automatically append the parent's id
        if ($admin->isChild() && $admin->hasRequest() && $admin->getRequest()->attributes->has($admin->getParent()->getIdParameter())) {
            // twig template does not accept variable hash key ... so cannot use admin.idparameter ...
            // switch value
            if (isset($parameters['id'])) {
                $parameters[$admin->getIdParameter()] = $parameters['id'];
                unset($parameters['id']);
            }

            $parameters[$admin->getParent()->getIdParameter()] = $admin->getRequest()->attributes->get($admin->getParent()->getIdParameter());
        }

        // if the admin is linked to a parent FieldDescription (ie, embedded widget)
        if ($admin->hasParentFieldDescription()) {
            // merge link parameter if any provided by the parent field
            $parameters = array_merge($parameters, $admin->getParentFieldDescription()->getOption('link_parameters', array()));

            $parameters['uniqid'] = $admin->getUniqid();
            $parameters['code'] = $admin->getCode();
            $parameters['pcode'] = $admin->getParentFieldDescription()->getAdmin()->getCode();
            $parameters['puniqid'] = $admin->getParentFieldDescription()->getAdmin()->getUniqid();
        }

        if ($name == 'update' || substr($name, -7) == '|update') {
            $parameters['uniqid'] = $admin->getUniqid();
            $parameters['code'] = $admin->getCode();
        }

        // allows to define persistent parameters
        if ($admin->hasRequest()) {
            $parameters = array_merge($admin->getPersistentParameters(), $parameters);
        }

        $code = $this->getCode($admin, $name);

        if (!array_key_exists($code, $this->caches)) {
            throw new \RuntimeException(sprintf('unable to find the route `%s`', $code));
        }

        return array(
            'route' => $this->caches[$code],
            'routeParameters' => $parameters,
            'routeAbsolute' => $absolute,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasAdminRoute(AdminInterface $admin, $name)
    {
        return array_key_exists($this->getCode($admin, $name), $this->caches);
    }

    /**
     * @param AdminInterface $admin
     * @param string         $name
     *
     * @return string
     */
    private function getCode(AdminInterface $admin, $name)
    {
        $this->loadCache($admin);

        if ($admin->isChild()) {
            return $admin->getBaseCodeRoute().'.'.$name;
        }

        // someone provide the fullname
        if (array_key_exists($name, $this->caches)) {
            return $name;
        }

        // someone provide a code, so it is a child
        if (strpos($name, '.')) {
            return $admin->getCode().'|'.$name;
        }

        return $admin->getCode().'.'.$name;
    }

    /**
     * @param AdminInterface $admin
     */
    private function loadCache(AdminInterface $admin)
    {
        if ($admin->isChild()) {
            $this->loadCache($admin->getParent());
        } else {
            if (in_array($admin->getCode(), $this->loaded)) {
                return;
            }

            $this->caches = array_merge($this->cache->load($admin), $this->caches);

            $this->loaded[] = $admin->getCode();
        }
    }
}
