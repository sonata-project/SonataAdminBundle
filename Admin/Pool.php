<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\BaseApplicationBundle\Admin;


class Pool
{
    protected $container = null;
    
    protected $configuration = array();

    protected $instances = array();

    public function addConfiguration($code, $configuration)
    {
        $configuration['code'] = $code;
        
        $this->configuration[$code] = $configuration;
    }

    public function getGroups()
    {

        $groups = array();

        foreach($this->configuration as $configuration) {

            if(!isset($groups[$configuration['group']])) {
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
    public function getAdminConfigurationByControllerName($name)
    {
        $configuration_code = false;
        foreach($this->configuration as $code => $configuration) {
            if($configuration['controller'] == $name) {
                $configuration_code = $code;
            }
        }

        if(!$configuration_code) {
            throw new \RuntimeException(sprintf('Unable to retrieve the admin object for controller `%s`', $name));
        }

        return $this->getInstance($configuration_code);
    }

    public function getConfigurationByClass($class)
    {

        $configuration_code = false;

        foreach($this->configuration as $code => $configuration) {

            if($configuration['entity'] == $class) {
                $configuration_code = $code;
                break;
            }
        }

        if(!$configuration_code) {
            throw new \RuntimeException(sprintf('Unable to retrieve the admin object for class `%s`', $class));
        }


        return $this->getInstance($code);
    }

    public function getInstance($code)
    {
        if(!isset($this->instances[$code])) {
            
            $class = $this->configuration[$code]['class'];
            $this->instances[$code] = new $class;
            $this->instances[$code]->setContainer($this->getContainer());
            $this->instances[$code]->setConfigurationPool($this);
            $this->instances[$code]->setCode($code);
            $this->instances[$code]->setLabel($this->configuration[$code]['label']);
            $this->instances[$code]->configure();
        }

        return $this->instances[$code];
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setInstances($instances)
    {
        $this->instances = $instances;
    }

    public function getInstances()
    {
        return $this->instances;
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