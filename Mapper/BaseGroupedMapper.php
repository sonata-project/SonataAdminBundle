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
    protected $currentTab;

    abstract protected function getGroups();
    abstract protected function getTabs();

    abstract protected function setGroups(array $groups);
    abstract protected function setTabs(array $tabs);

    /**
     * @param string $name
     * @param array  $options
     *
     * @return \Sonata\AdminBundle\Mapper\BaseGroupedMapper
     */
    public function with($name, array $options = array())
    {
        if (array_key_exists("tab",$options) && $options["tab"]) {
            $tabs = $this->getTabs();
            if ($this->currentTab) {
                if ($tabs[$this->currentTab]["auto_created"]) {
                    throw new \Exception("New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields");
                } else {
                    throw new \Exception("You should close previous tab with end() before adding new tab");
                }
            } elseif ($this->currentGroup) {
                throw new \Exception("You should open tab before adding new groups");
            }
            if (!isset($tabs[$name])) {
                $tabs[$name] = array();
            }

            $tabs[$name] = array_merge(array(
                'auto_created'       => false,
                'collapsed'          => false,
                'class'              => false,
                'groups'             => array(),
                'description'        => false,
                'translation_domain' => null,
            ), $tabs[$name], $options);

            $this->currentTab = $name;
        } else {
            if ($this->currentGroup) {
                throw new \Exception("You should close previous group with end() before adding new tab");
            }
            if (!$this->currentTab) {
                $this->with($this->admin->getLabel(), array("tab"=>true, "auto_created"=>true)); // add new tab automatically
            }
            $name = $this->currentTab.".".$name; // groups with the same name can be on different tabs, so we prefix them in order to make unique group name
            $groups = $this->getGroups();
            if (!isset($groups[$name])) {
                $groups[$name] = array();
            }

            $groups[$name] = array_merge(array(
                'collapsed'          => false,
                'class'              => false,
                'fields'             => array(),
                'description'        => false,
                'translation_domain' => null,
                ),$groups[$name],$options);

            $this->currentGroup = $name;
            $this->setGroups($groups);
            $tabs = $this->getTabs();
        }

        if ($this->currentGroup && !in_array($this->currentGroup,$tabs[$this->currentTab]["groups"])) {
            $tabs[$this->currentTab]["groups"][] = $this->currentGroup;
        }
        $this->setTabs($tabs);

        return $this;
    }

    /**
     * @return \Sonata\AdminBundle\Mapper\BaseGroupedMapper
     */
    public function end()
    {
        if ($this->currentGroup !== null) {
            $this->currentGroup = null;
        } elseif ($this->currentTab !== null) {
            $this->currentTab = null;
        } else {
            throw new \Exception("No open tabs or groups, you cannot use end()");
        }

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
            $this->with($this->admin->getLabel(), array('auto_created'=>true));
        }

        return $this->currentGroup;
    }

}
