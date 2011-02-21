<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BaseApplicationBundle\Admin;


class Pool
{
    protected $container = null;
    
    protected $configuration = array();

    public function addConfiguration($code, $configuration)
    {
        $configuration['code'] = $code;
        
        $this->configuration[$code] = $configuration;
    }

    public function getGroups()
    {

        $groups = array();

        foreach ($this->configuration as $configuration) {

            if (!isset($groups[$configuration['group']])) {
                $groups[$configuration['group']] = array();
            }

            $groups[$configuration['group']][$configuration['code']] = $this->getInstance($configuration['code']);
        }

        return $groups;
    }

    /**
     * The admin classes are lazy loaded to avoid overhead
     *
     * @throws RuntimeException
     * @param  $name
     * @return
     */
    public function getAdminByControllerName($name)
    {
        $configuration_code = false;
        foreach ($this->configuration as $code => $configuration) {
            if ($configuration['controller'] == $name) {
                $configuration_code = $code;
            }
        }

        if (!$configuration_code) {
            return null;
        }

        return $this->getInstance($configuration_code);
    }

    /**
     * return the admin related to the given $class
     *
     * @param string $class
     * @return Admin|null
     */
    public function getAdminByClass($class)
    {

        $configuration_code = false;

        foreach ($this->configuration as $code => $configuration) {

            if ($configuration['entity'] == $class) {
                $configuration_code = $code;
                break;
            }
        }

        if (!$configuration_code) {
            return null;
        }

        return $this->getInstance($code);
    }

    /**
     * return the admin related to the given $actionName
     *
     * @param string $actionName
     * @return Admin|null
     */
    public function getAdminByActionName($actionName)
    {
        $codes = explode('.', $actionName);

        $instance = false;
        foreach ($codes as $pos => $code) {
            if ($pos == 0) {
                $instance = $this->getInstance($code);
            } else if($instance->hasChildren()) {
                if(!$instance->hasChild($code)) {
                    break;
                }

                $instance = $instance->getChild($code);

                if(!$instance instanceof Admin) {
                    throw new \RuntimeException(sprintf('unable to retrieve the child admin related to the actionName : `%s`', $actionName));
                }
                
            } else {
                break;
            }
        }

        if(!$instance instanceof Admin) {
            throw new \RuntimeException(sprintf('unable to retrieve the admin related to the actionName : `%s`', $actionName));
        }

        return $instance;
    }

    /**
     *
     * return a new admin instance depends on the given code
     *
     * @param $code
     * @return
     */
    public function getInstance($code)
    {
        if(!isset($this->configuration[$code])) {
            throw new \RuntimeException(sprintf('The code `%s` does not exist', $code));
        }

        return $this->getInstanceFromConfiguration($code, $this->configuration[$code]);
    }

    protected function getInstanceFromConfiguration($code, $configuration)
    {
        $class = $configuration['class'];
        
        $instance = new $class($code, $this->getContainer(), $configuration['entity']);
        $instance->setLabel($configuration['label']);

        if(isset($configuration['children'])) {
            foreach($configuration['children'] as $code => $child) {
                $instance->addChild($code, $this->getInstanceFromConfiguration($code, $child));
            }
        }

        return $instance;
    }

    /**
     * return a group of admin instance
     *
     * @return array
     */
    public function getInstances()
    {
        $instances = array();
        foreach ($this->configuration as $code => $configuration) {
            $instances[] = $this->getInstance($code);
        }

        return $instances;
    }

    public function setConfiguration(array $configuration = array())
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }
}