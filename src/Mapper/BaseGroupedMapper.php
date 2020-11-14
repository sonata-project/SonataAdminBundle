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
     * @param array<string, mixed> $options
     *
     * @throws \LogicException
     *
     * @return static
     */
    public function with(string $name, array $options = []): self
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
            'label' => $this->admin->getLabelTranslatorStrategy()->getLabel($name, $this->getName(), 'group'),
            'translation_domain' => null,
            'name' => $name,
            'box_class' => 'box box-primary',
            'empty_message' => 'message_form_group_empty',
            'empty_message_translation_domain' => 'SonataAdminBundle',
        ];

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
     * @return static
     */
    public function ifTrue(bool $bool): self
    {
        $this->apply[] = true === $bool;

        return $this;
    }

    /**
     * Only nested add if the condition match false.
     *
     * @return static
     */
    public function ifFalse(bool $bool): self
    {
        $this->apply[] = false === $bool;

        return $this;
    }

    /**
     * @throws \LogicException
     *
     * @return static
     */
    public function ifEnd(): self
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
     * @param array<string, mixed> $options
     *
     * @return static
     */
    public function tab(string $name, array $options = []): self
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
    public function end(): self
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
     */
    public function hasOpenTab(): bool
    {
        return null !== $this->currentTab;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    abstract protected function getGroups(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    abstract protected function getTabs(): array;

    /**
     * @param array<string, array<string, mixed>> $groups
     */
    abstract protected function setGroups(array $groups): void;

    /**
     * @param array<string, array<string, mixed>> $tabs
     */
    abstract protected function setTabs(array $tabs): void;

    abstract protected function getName(): string;

    /**
     * @return array<string, mixed>
     */
    protected function addFieldToCurrentGroup(string $fieldName): array
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
     */
    protected function getCurrentGroupName(): string
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
