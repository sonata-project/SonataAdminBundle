<?php

declare(strict_types=1);

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
     * @var bool[]
     */
    protected $apply = [];

    /**
     * Add new group or tab (if parameter "tab=true" is available in options).
     *
     * @param string               $name
     * @param array<string, mixed> $options
     *
     * @throws \LogicException
     *
     * @return static
     */
    public function with($name, array $options = [])
    {
        if (!$this->shouldApply()) {
            return $this;
        }

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
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
        ];

        // NEXT_MAJOR: remove this code
        if ($this->admin instanceof AbstractAdmin && $pool = $this->admin->getConfigurationPool('sonata_deprecation_mute')) {
            if ($pool->getContainer('sonata_deprecation_mute')->getParameter('sonata.admin.configuration.translate_group_label')) {
                $defaultOptions['label'] = $this->admin->getLabelTranslatorStrategy()->getLabel($name, $this->getName(), 'group');
            }
        }

        $code = $name;

        // Open
        if (\array_key_exists('tab', $options) && $options['tab']) {
            $tabs = $this->getTabs();

            if ($this->currentTab) {
                if (isset($tabs[$this->currentTab]['auto_created']) && true === $tabs[$this->currentTab]['auto_created']) {
                    throw new \LogicException('New tab was added automatically when you have added field or group. You should close current tab before adding new one OR add tabs before adding groups and fields.');
                }

                throw new \LogicException(sprintf(
                    'You should close previous tab "%s" with end() before adding new tab "%s".',
                    $this->currentTab,
                    $name
                ));
            }

            if ($this->currentGroup) {
                throw new \LogicException(sprintf('You should open tab before adding new group "%s".', $name));
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
                throw new \LogicException(sprintf(
                    'You should close previous group "%s" with end() before adding new tab "%s".',
                    $this->currentGroup,
                    $name
                ));
            }

            if (!$this->currentTab) {
                // no tab define
                $this->with('default', [
                    'tab' => true,
                    'auto_created' => true,
                    'translation_domain' => $options['translation_domain'] ?? null,
                ]); // add new tab automatically
            }

            // if no tab is selected, we go the the main one named '_' ..
            if ('default' !== $this->currentTab) {
                // groups with the same name can be on different tabs, so we prefix them in order to make unique group name
                $code = sprintf('%s.%s', $this->currentTab, $name);
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

        if ($this->currentGroup && isset($tabs[$this->currentTab]) && !\in_array($this->currentGroup, $tabs[$this->currentTab]['groups'], true)) {
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
     * @return static
     */
    public function ifTrue($bool)
    {
        $this->apply[] = true === $bool;

        return $this;
    }

    /**
     * Only nested add if the condition match false.
     *
     * @param bool $bool
     *
     * @return static
     */
    public function ifFalse($bool)
    {
        $this->apply[] = false === $bool;

        return $this;
    }

    /**
     * @throws \LogicException
     *
     * @return static
     */
    public function ifEnd()
    {
        if (empty($this->apply)) {
            throw new \LogicException('No open ifTrue() or ifFalse(), you cannot use ifEnd()');
        }

        array_pop($this->apply);

        return $this;
    }

    /**
     * Add new tab.
     *
     * @param string               $name
     * @param array<string, mixed> $options
     *
     * @return static
     */
    public function tab($name, array $options = [])
    {
        return $this->with($name, array_merge($options, ['tab' => true]));
    }

    /**
     * Close the current group or tab.
     *
     * @throws \LogicException
     *
     * @return static
     */
    public function end()
    {
        if (!$this->shouldApply()) {
            return $this;
        }

        if (null !== $this->currentGroup) {
            $this->currentGroup = null;
        } elseif (null !== $this->currentTab) {
            $this->currentTab = null;
        } else {
            throw new \LogicException('No open tabs or groups, you cannot use end()');
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
     * Removes a group.
     *
     * @param string $group          The group to delete
     * @param string $tab            The tab the group belongs to, defaults to 'default'
     * @param bool   $deleteEmptyTab Whether or not the Tab should be deleted, when the deleted group leaves the tab empty after deletion
     *
     * @return static
     */
    public function removeGroup($group, $tab = 'default', $deleteEmptyTab = false)
    {
        $groups = $this->getGroups();

        // When the default tab is used, the tabname is not prepended to the index in the group array
        if ('default' !== $tab) {
            $group = sprintf('%s.%s', $tab, $group);
        }

        if (isset($groups[$group])) {
            foreach ($groups[$group]['fields'] as $field) {
                $this->remove($field);
            }
        }
        unset($groups[$group]);

        $tabs = $this->getTabs();
        $key = array_search($group, $tabs[$tab]['groups'], true);

        if (false !== $key) {
            unset($tabs[$tab]['groups'][$key]);
        }
        if ($deleteEmptyTab && 0 === \count($tabs[$tab]['groups'])) {
            unset($tabs[$tab]);
        }

        $this->setTabs($tabs);
        $this->setGroups($groups);

        return $this;
    }

    /**
     * Removes a tab.
     *
     * @return static
     */
    public function removeTab(string $tab): self
    {
        $groups = $this->getGroups();
        $tabs = $this->getTabs();

        foreach ($tabs[$tab]['groups'] as $group) {
            if (isset($groups[$group])) {
                foreach ($groups[$group]['fields'] as $field) {
                    $this->remove($field);
                }
            }

            unset($groups[$group]);
        }

        unset($tabs[$tab]);

        $this->setTabs($tabs);
        $this->setGroups($groups);

        return $this;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    abstract protected function getGroups();

    /**
     * @return array<string, array<string, mixed>>
     */
    abstract protected function getTabs();

    /**
     * @param array<string, array<string, mixed>> $groups
     */
    abstract protected function setGroups(array $groups);

    /**
     * @param array<string, array<string, mixed>> $tabs
     */
    abstract protected function setTabs(array $tabs);

    /**
     * NEXT_MAJOR: make this method abstract.
     *
     * @return string
     */
    protected function getName()
    {
        @trigger_error(sprintf(
            '%s should be implemented and will be abstract in 4.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return 'default';
    }

    /**
     * Add the field name to the current group.
     *
     * @param string $fieldName
     *
     * @return array<string, mixed>
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
            $label = $this->admin->getLabel();

            if (null === $label) {
                $this->with('default', ['auto_created' => true]);
            } else {
                $this->with($label, [
                    'auto_created' => true,
                    'translation_domain' => $this->admin->getTranslationDomain(),
                ]);
            }
        }

        return $this->currentGroup;
    }

    /**
     * Check if all apply conditions are respected.
     */
    final protected function shouldApply(): bool
    {
        return !\in_array(false, $this->apply, true);
    }
}
