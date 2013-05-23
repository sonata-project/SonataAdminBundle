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

    /**
     * The name of the current group
     *
     * @var string
     */
    protected $currentGroup = false;
    
    /**
     * @var array
     */
    private $groups = array();
    
    /**
     * @param string $name
     * @param array  $options
     *
     * @return \Sonata\AdminBundle\Mapper\BaseGroupedMapper
     */
    public function with($name, array $options = array())
    {
        if (!isset($this->groups[$name])) {
            $this->groups[$name] = array(
                'collapsed'   => false,
                'fields'      => array(),
                'description' => false
            );
        }

        $this->groups[$name] = array_merge($this->groups[$name], $options);

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
        $currentGroup = $this->getCurrentGroupName();
        $this->groups[$currentGroup]['fields'][$fieldName] = $fieldName;
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
        if ( ! $this->currentGroup) {
            $this->with($this->admin->getLabel());
        }
        return $this->currentGroup;
    }
    
    /**
     * @return array
     */
    public function getGroups() {
        return $this->groups;
    }
    
    /**
     * @param array $keys field names
     *
     * @return \Sonata\AdminBundle\Form\FormMapper
     */
    public function reorder(array $keys)
    {
        $currentGroup = $this->getCurrentGroupName();
        $this->groups[$currentGroup]['fields'] = array_merge(array_flip($keys), $this->groups[$currentGroup]['fields']);
        
        return $this;
    }
    
}
