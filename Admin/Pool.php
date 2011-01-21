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
     *
     * return a new admin instance depends on the given code
     *
     * @param $code
     * @return
     */
    public function getInstance($code)
    {

        $class = $this->configuration[$code]['class'];
        $instance = new $class;
        $instance->setContainer($this->getContainer());
        $instance->setConfigurationPool($this);
        $instance->setCode($code);
        $instance->setLabel($this->configuration[$code]['label']);
        $instance->configure();

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