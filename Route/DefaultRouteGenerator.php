<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Sonata\AdminBundle\Route;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Routing\RouterInterface;

class DefaultRouteGenerator implements RouteGeneratorInterface
{
    private $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @return string
     */
    public function generate($name, array $parameters = array(), $absolute = false)
    {
        return $this->router->generate($name, $parameters, $absolute);
    }

    /**
     *
     * @param \Sonata\AdminBundle\Admin\AdminInterface $admin
     * @param string                                   $name
     * @param array                                    $parameters
     * @param bool                                     $absolute

     * @throws \RuntimeException

     * @return string
     */
    public function generateUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = false)
    {
        $arrayRoute = $this->generateMenuUrl($admin, $name, $parameters, $absolute);

        return $this->router->generate($arrayRoute['route'], $arrayRoute['routeParameters'], $arrayRoute['routeAbsolute']);
    }

    /**
     * Generates KNPMenu array parameters for menu route
     *
     * @param AdminInterface $admin
     * @param string         $name
     * @param array          $parameters
     * @param bool           $absolute
     *
     * @return array
     * @throws \RuntimeException
     */
    public function generateMenuUrl(AdminInterface $admin, $name, array $parameters = array(), $absolute = false)
    {
        if (!$admin->isChild()) {
            if (strpos($name, '.')) {
                $name = $admin->getCode().'|'.$name;
            } else {
                $name = $admin->getCode().'.'.$name;
            }
        }
        // if the admin is a child we automatically append the parent's id
        else {
            $name = $admin->getBaseCodeRoute().'.'.$name;

            // twig template does not accept variable hash key ... so cannot use admin.idparameter ...
            // switch value
            if (isset($parameters['id'])) {
                $parameters[$admin->getIdParameter()] = $parameters['id'];
                unset($parameters['id']);
            }

            $parameters[$admin->getParent()->getIdParameter()] = $admin->getRequest()->get($admin->getParent()->getIdParameter());
        }

        // if the admin is linked to a parent FieldDescription (ie, embedded widget)
        if ($admin->hasParentFieldDescription()) {
            // merge link parameter if any provided by the parent field
            $parameters = array_merge($parameters, $admin->getParentFieldDescription()->getOption('link_parameters', array()));

            $parameters['uniqid']  = $admin->getUniqid();
            $parameters['code']    = $admin->getCode();
            $parameters['pcode']   = $admin->getParentFieldDescription()->getAdmin()->getCode();
            $parameters['puniqid'] = $admin->getParentFieldDescription()->getAdmin()->getUniqid();
        }

        if ($name == 'update' || substr($name, -7) == '|update') {
            $parameters['uniqid'] = $admin->getUniqid();
            $parameters['code']   = $admin->getCode();
        }

        // allows to define persistent parameters
        if ($admin->hasRequest()) {
            $parameters = array_merge($admin->getPersistentParameters(), $parameters);
        }

        $route = $admin->getRoute($name);

        if (!$route) {
            throw new \RuntimeException(sprintf('unable to find the route `%s`', $name));
        }

        return array(
            'route'           => $route->getDefault('_sonata_name'),
            'routeParameters' => $parameters,
            'routeAbsolute'   => $absolute
        );
    }
}
