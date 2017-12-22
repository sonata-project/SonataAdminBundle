<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Mapper;

use Sonata\AdminBundle\Admin\AbstractAdmin;

/**
 * This class is used to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseGroupedMapper extends BaseMapper
{
    /**
     * @var string|null
     */
    protected $currentGroup;

    /**
     * @var string|null
     */
    protected $currentTab;

    /**
     * @var bool|null
     */
    protected $apply;

    /**
     * Add new group or tab (if parameter "tab=true" is available in options).
     *
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function with($name, array $options = [])
    {
        /*
         * The current implementation should work with the following workflow:
         *
         *     $formMapper
         *        ->with('group1')
         *            ->add('username')
         *            ->add('password')
         *        ->end()
         *        ->with('tab1', ['tab' => true])
         *            ->with('group1')
         *                ->add('username')
         *                ->add('password')
         *            ->end()
         *            ->with('group2', ['collapsed' => true])
         *                ->add('enabled')
         *                ->add('createdAt')
         *            ->end()
         *        ->end();
         *
         */
        $defaultOptions = [
            'collapsed' => false,
            'class' => false,
            'description' => false,
            'label' => $name, // NEXT_MAJOR: Remove this line and uncomment the next one
//            'label' => $this->admin->getLabelTranslatorStrategy()->getLabel($name, $this->getName(), 'group'),
            'translation_domain' => null,
            'name' => $name,
            'box_class' => 'box box-primary',
        ];

        // NEXT_MAJOR: remove this code
        if ($this->admin instanceof AbstractAdmin && $pool = $this->admin->getConfigurationPool()) {
            if ($pool->getContainer()->getParameter('sonata.admin.configuration.translate_group_label')) {
                $defaultOptions['label'] = $this->admin->getLabelTranslatorStrategy()->getLabel($name, $this->getName(), 'group');
            }
        }

        $code = $name;

        // Open
        if (array_key_exists('tab', $options) && $options['tab']) {
            $tabs = $this->getTabs();

            if ($this->currentTab) {
                if (isset($tabs[$this->currentTab]['auto_created']) && true === $tabs[$this->currentTab]['auto_created']) {
                    throw new \RuntimeException('New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields.');
                }

                throw new \RuntimeException(sprintf('You should close previous tab "%s" with end() before adding new tab "%s".', $this->currentTab, $name));
            } elseif ($this->currentGroup) {
                throw new \RuntimeException(sprintf('You should open tab before adding new group "%s".', $name));
            }

            if (!isset($tabs[$name])) {
                $tabs[$name] = [];
            }

            $tabs[$code] = array_merge($defaultOptions, [
                'auto_created' => false,
                'groups' => [],
            ], $tabs[$code], $options);

            $this->currentTab = $code;
        } else {
            if ($this->currentGroup) {
                throw new \RuntimeException(sprintf('You should close previous group "%s" with end() before adding new tab "%s".', $this->currentGroup, $name));
            }

            if (!$this->currentTab) {
                // no tab define
                $this->with('default', [
                    'tab' => true,
                    'auto_created' => true,
                    'translation_domain' => isset($options['translation_domain']) ? $options['translation_domain'] : null,
                ]); // add new tab automatically
            }

            // if no tab is selected, we go the the main one named '_' ..
            if ('default' !== $this->currentTab) {
                $code = $this->currentTab.'.'.$name; // groups with the same name can be on different tabs, so we prefix them in order to make unique group name
            }

            $groups = $this->getGroups();
            if (!isset($groups[$code])) {
                $groups[$code] = [];
            }

            $groups[$code] = array_merge($defaultOptions, [
                'fields' => [],
            ], $groups[$code], $options);

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
     * Only nested add if the condition match true.
     *
     * @param bool $bool
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function ifTrue($bool)
    {
        if (null !== $this->apply) {
            throw new \RuntimeException('Cannot nest ifTrue or ifFalse call');
        }

        $this->apply = (true === $bool);

        return $this;
    }

    /**
     * Only nested add if the condition match false.
     *
     * @param bool $bool
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function ifFalse($bool)
    {
        if (null !== $this->apply) {
            throw new \RuntimeException('Cannot nest ifTrue or ifFalse call');
        }

        $this->apply = (false === $bool);

        return $this;
    }

    /**
     * @return $this
     */
    public function ifEnd()
    {
        $this->apply = null;

        return $this;
    }

    /**
     * Add new tab.
     *
     * @param string $name
     *
     * @return $this
     */
    public function tab($name, array $options = [])
    {
        return $this->with($name, array_merge($options, ['tab' => true]));
    }

    /**
     * Close the current group or tab.
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function end()
    {
        if (null !== $this->currentGroup) {
            $this->currentGroup = null;
        } elseif (null !== $this->currentTab) {
            $this->currentTab = null;
        } else {
            throw new \RuntimeException('No open tabs or groups, you cannot use end()');
        }

        return $this;
    }

    /**
     * Returns a boolean indicating if there is an open tab at the moment.
     *
     * @return bool
     */
    public function hasOpenTab()
    {
        return null !== $this->currentTab;
    }

    /**
     * @return array
     */
    abstract protected function getGroups();

    /**
     * @return array
     */
    abstract protected function getTabs();

    abstract protected function setGroups(array $groups);

    abstract protected function setTabs(array $tabs);

    /**
     * NEXT_MAJOR: make this method abstract.
     *
     * @return string
     */
    protected function getName()
    {
        @trigger_error(__METHOD__.' should be implemented and will be abstract in 4.0.', E_USER_DEPRECATED);

        return 'default';
    }

    /**
     * Add the field name to the current group.
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
     * sure a valid group name is currently selected.
     *
     * Note that this can have the side effect to change the 'group' value
     * returned by the getGroup function
     *
     * @return string
     */
    protected function getCurrentGroupName()
    {
        if (!$this->currentGroup) {
            $this->with($this->admin->getLabel(), ['auto_created' => true]);
        }

        return $this->currentGroup;
    }
}
