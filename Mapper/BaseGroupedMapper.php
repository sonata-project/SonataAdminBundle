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
namespace Sonata\AdminBundle\Mapper;

/**
 * This class is used to simulate the Form API
 *
 */
abstract class BaseGroupedMapper extends BaseMapper
{

    protected $currentGroup;
    
    protected abstract function getGroups();
    
    protected abstract function setGroups(array $groups);
    
    /**
     * @param string $name
     * @param array  $options
     *
     * @return \Sonata\AdminBundle\Mapper\BaseGroupedMapper
     */
    public function with($name, array $options = array())
    {
        $groups = $this->getGroups();
        
        if (!isset($groups[$name])) {
            $groups[$name] = array();
        }

        $groups[$name] = array_merge(array(
            'collapsed'          => false,
            'fields'             => array(),
            'description'        => false,
            'translation_domain' => null,
        ), $groups[$name], $options);
        
        $this->setGroups($groups);

        $this->currentGroup = $name;

        return $this;
    }
    
    /**
     * @return \Sonata\AdminBundle\Mapper\BaseGroupedMapper
     */
    public function end()
    {
        $this->currentGroup = null;

        return $this;
    }

    /**
     * Add the fieldname to the current group
     * 
     * @param string $fieldName
     */
    protected function addFieldToCurrentGroup($fieldName) 
    {
        // Note this line must happen before the next line. 
        // See https://github.com/sonata-project/SonataAdminBundle/pull/1351
        $currentGroup = $this->getCurrentGroupName();
        $groups = $this->getGroups();
        $groups[$currentGroup]['fields'][$fieldName] = $fieldName;
        $this->setGroups($groups);

        return $groups[$currentGroup];
    }

    /**
     * Return the name of the currently selected group. The method also makes 
     * sure a valid group name is currently selected
     * 
     * Note that this can have the side effect to change the "group" value
     * returned by the getGroup function
     * 
     * @return string
     */
    protected function getCurrentGroupName() 
    {
        if (!$this->currentGroup) {
            $this->with($this->admin->getLabel());
        }
        return $this->currentGroup;
    }
    
}
