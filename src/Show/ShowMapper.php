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
 */
final class ShowMapper extends BaseGroupedMapper
{
    /**
     * @var ShowBuilderInterface
     */
    protected $builder;

    /**
     * @var FieldDescriptionCollection
     */
    private $list;

    public function __construct(
        ShowBuilderInterface $showBuilder,
        FieldDescriptionCollection $list,
        AdminInterface $admin
    ) {
        parent::__construct($showBuilder, $admin);
        $this->list = $list;
    }

    /**
     * @param FieldDescriptionInterface|string $name
     * @param array<string, mixed>             $fieldDescriptionOptions
     *
     * @throws \LogicException
     *
     * @return static
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
            if (!$this->admin->hasShowFieldDescription($name)) {
                $fieldDescription = $this->admin->createFieldDescription(
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
            $fieldDescription->setOption('label', $this->admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'show', 'label'));
        }

        $fieldDescription->setOption('safe', $fieldDescription->getOption('safe', false));

        if (!isset($fieldDescriptionOptions['role']) || $this->admin->isGranted($fieldDescriptionOptions['role'])) {
            // add the field with the FormBuilder
            $this->builder->addField($this->list, $type, $fieldDescription);
        }

        return $this;
    }

    public function get(string $name): FieldDescriptionInterface
    {
        return $this->list->get($name);
    }

    public function has(string $key): bool
    {
        return $this->list->has($key);
    }

    public function remove(string $key): self
    {
        $this->admin->removeShowFieldDescription($key);
        $this->list->remove($key);

        return $this;
    }

    public function keys(): array
    {
        return array_keys($this->list->getElements());
    }

    public function reorder(array $keys): self
    {
        $this->admin->reorderShowGroup($this->getCurrentGroupName(), $keys);

        return $this;
    }

    protected function getGroups(): array
    {
        return $this->admin->getShowGroups();
    }

    protected function setGroups(array $groups): void
    {
        $this->admin->setShowGroups($groups);
    }

    protected function getTabs(): array
    {
        return $this->admin->getShowTabs();
    }

    protected function setTabs(array $tabs): void
    {
        $this->admin->setShowTabs($tabs);
    }

    protected function getName(): string
    {
        return 'show';
    }
}
