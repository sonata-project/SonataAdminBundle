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

namespace Sonata\AdminBundle\Show;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

/**
 * This class is used to simulate the Form API.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-import-type FieldDescriptionOptions from \Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface
 */
final class ShowMapper extends BaseGroupedMapper
{
    /**
     * @var ShowBuilderInterface
     */
    private $builder;

    /**
     * @var FieldDescriptionCollection<FieldDescriptionInterface>
     */
    private $list;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @param FieldDescriptionCollection<FieldDescriptionInterface> $list
     * @param AdminInterface<object>                                $admin
     */
    public function __construct(
        ShowBuilderInterface $showBuilder,
        FieldDescriptionCollection $list,
        AdminInterface $admin
    ) {
        $this->admin = $admin;
        $this->builder = $showBuilder;
        $this->list = $list;
    }

    public function getAdmin(): AdminInterface
    {
        return $this->admin;
    }

    /**
     * @param FieldDescriptionInterface|string $name
     *
     * @throws \LogicException
     *
     * @return static
     *
     * @phpstan-param FieldDescriptionOptions $fieldDescriptionOptions
     */
    public function add($name, ?string $type = null, array $fieldDescriptionOptions = []): self
    {
        if (!$this->shouldApply()) {
            return $this;
        }

        if ($name instanceof FieldDescriptionInterface) {
            $fieldDescription = $name;
            $fieldDescription->mergeOptions($fieldDescriptionOptions);
        } elseif (\is_string($name)) {
            if (!$this->getAdmin()->hasShowFieldDescription($name)) {
                $fieldDescription = $this->getAdmin()->createFieldDescription(
                    $name,
                    $fieldDescriptionOptions
                );
            } else {
                throw new \LogicException(sprintf(
                    'Duplicate field name "%s" in show mapper. Names should be unique.',
                    $name
                ));
            }
        } else {
            throw new \TypeError(
                'Unknown field name in show mapper.'
                    .' Field name should be either of FieldDescriptionInterface interface or string.'
            );
        }

        $fieldKey = ($name instanceof FieldDescriptionInterface) ? $name->getName() : $name;

        $this->addFieldToCurrentGroup($fieldKey);

        if (null === $fieldDescription->getLabel()) {
            $fieldDescription->setOption('label', $this->getAdmin()->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label'));
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        if (!isset($fieldDescriptionOptions['role']) || $this->getAdmin()->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the FormBuilder
            $this->builder->addField($this->list, $type, $fieldDescription);
        }

        return $this;
    }

    public function get(string $key): FieldDescriptionInterface
    {
        return $this->list->get($key);
    }

    public function has(string $key): bool
    {
        return $this->list->has($key);
    }

    /**
     * @return static
     */
    public function remove(string $key): self
    {
        $this->getAdmin()->removeShowFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    public function keys(): array
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys): self
    {
        $this->getAdmin()->reorderShowGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    protected function getGroups(): array
    {
        return $this->getAdmin()->getShowGroups();
    }

    protected function setGroups(array $groups): void
    {
        $this->getAdmin()->setShowGroups($groups);
    }

    protected function getTabs(): array
    {
        return $this->getAdmin()->getShowTabs();
    }

    protected function setTabs(array $tabs): void
    {
        $this->getAdmin()->setShowTabs($tabs);
    }

    protected function getName(): string
    {
        return 'show';
    }
}
