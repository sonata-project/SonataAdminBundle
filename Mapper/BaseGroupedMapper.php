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

    protected $apply;

    abstract protected function getGroups();
    abstract protected function getTabs();

    abstract protected function setGroups(array $groups);
    abstract protected function setTabs(array $tabs);

    /**
     * Add new group or tab (if parameter "tab=true" is available in options)
     *
     * @param string $name
     * @param array  $options
     *
     * @return BaseGroupedMapper
     *
     * @throws \RuntimeException
     */
    public function with($name, array $options = array())
    {
        /**
         * The current implementation should work with the following workflow:
         *
         *     $formMapper
         *        ->with('group1')
         *            ->add('username')
         *            ->add('password')
         *        ->end()
         *        ->with('tab1', array('tab' => true))
         *            ->with('group1')
         *                ->add('username')
         *                ->add('password')
         *            ->end()
         *            ->with('group2', array('collapsed' => true))
         *                ->add('enabled')
         *                ->add('createdAt')
         *            ->end()
         *        ->end();
         *
         */
        $defaultOptions = array(
            'collapsed'          => false,
            'class'              => false,
            'description'        => false,
            'translation_domain' => null,
            'name'               => $name,
        );

        $code = $name;

        // Open
        if (array_key_exists('tab', $options) && $options['tab']) {
            $tabs = $this->getTabs();

            if ($this->currentTab) {
                if (isset($tabs[$this->currentTab]['auto_created']) && true === $tabs[$this->currentTab]['auto_created']) {
                    throw new \RuntimeException('New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields.');
                } else {
                    throw new \RuntimeException(sprintf('You should close previous tab "%s" with end() before adding new tab "%s".', $this->currentTab, $name));
                }
            } elseif ($this->currentGroup) {
                throw new \RuntimeException(sprintf('You should open tab before adding new group "%s".', $name));
            }

            if (!isset($tabs[$name])) {
                $tabs[$name] = array();
            }

            $tabs[$code] = array_merge($defaultOptions, array(
                'auto_created'       => false,
                'groups'             => array(),
            ), $tabs[$code], $options);

            $this->currentTab = $code;

        } else {

            if ($this->currentGroup) {
                throw new \RuntimeException(sprintf('You should close previous group "%s" with end() before adding new tab "%s".', $this->currentGroup, $name));
            }

            if (!$this->currentTab) {
                // no tab define
                $this->with('default', array(
                    'tab'                => true,
                    'auto_created'       => true,
                    'translation_domain' => isset($options['translation_domain']) ? $options['translation_domain'] : null
                )); // add new tab automatically
            }

            // if no tab is selected, we go the the main one named '_' ..
            if ($this->currentTab !== 'default') {
                $code = $this->currentTab.'.'.$name; // groups with the same name can be on different tabs, so we prefix them in order to make unique group name
            }

            $groups = $this->getGroups();
            if (!isset($groups[$code])) {
                $groups[$code] = array();
            }

            $groups[$code] = array_merge($defaultOptions, array(
                'fields' => array(),
            ), $groups[$code], $options);

            $this->currentGroup = $code;
            $this->setGroups($groups);
            $tabs = $this->getTabs();
        }

        if ($this->currentGroup && isset($tabs[$this->currentTab]) && !in_array($this->currentGroup, $tabs[$this->currentTab]['groups'])) {
            $tabs[$this->currentTab]['groups'][] = $this->currentGroup;
        }

        $this->setTabs($tabs);

        return $this;
    }

    /**
     * Only nested add if the condition match FALSE
     *
     * @param $bool
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function if_true($bool)
    {
        if (!$this->apply == null) {
            throw new \RuntimeException('Cannot nest ifTrue call');
        }

        $this->apply = ($bool === true);

        return $this;
    }

    /**
     * Only nested add if the condition match FALSE
     *
     * @param $bool
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function if_false($bool)
    {
        if (!$this->apply == null) {
            throw new \RuntimeException('Cannot nest ifTrue call');
        }

        $this->apply = ($bool === false);

        return $this;
    }

    /**
     * @return $this
     */
    public function end_if()
    {
        $this->apply = null;

        return $this;
    }

    /**
     * Add new tab
     *
     * @param string $name
     * @param array  $options
     *
     * @return BaseGroupedMapper
     */
    public function tab($name, array $options = array())
    {
        return $this->with($name, array_merge($options, array('tab' => true)));
    }

    /**
     * Close the current group or tab
     *
     * @return BaseGroupedMapper
     *
     * @throws \RuntimeException
     */
    public function end()
    {
        if ($this->currentGroup !== null) {
            $this->currentGroup = null;
        } elseif ($this->currentTab !== null) {
            $this->currentTab = null;
        } else {
            throw new \RuntimeException('No open tabs or groups, you cannot use end()');
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
     * Note that this can have the side effect to change the 'group' value
     * returned by the getGroup function
     *
     * @return string
     */
    protected function getCurrentGroupName()
    {
        if (!$this->currentGroup) {
            $this->with($this->admin->getLabel(), array('auto_created' => true));
        }

        return $this->currentGroup;
    }
}
