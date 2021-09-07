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

namespace Sonata\AdminBundle\FieldDescription;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\NoValueException;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * A FieldDescription hold the information about a field. A typical
 * admin instance contains different collections of fields.
 *
 * - form: used by the form
 * - list: used by the list
 * - filter: used by the list filter
 *
 * Some options are global across the different contexts, other are
 * context specifics.
 *
 * Global options:
 *   - type (m): define the field type (use to tweak the form or the list)
 *   - template (o): the template used to render the field
 *   - label (o): the name used (label in the form, title in the list)
 *   - accessor (o): the method or the property path to retrieve the related value
 *   - associated_property (o): the method or the property path to retrieve the "string"
 *                           representation of the collection element.
 *   - link_parameters (o): add link parameter to the related Admin class when
 *                           the Admin.generateUrl is called
 *
 * Form Field options:
 *   - field_type (o): the widget class to use to render the field
 *   - field_options (o): the options to give to the widget
 *   - edit (o): list|inline|standard (only used for associated admin)
 *      - list: open a popup where the user can search, filter and click on one field
 *               to select one item
 *      - inline: the associated form admin is embedded into the current form
 *      - standard: the associated admin is created through a popup
 *
 * List Field options:
 *   - identifier (o): if set to true a link appear on to edit the element
 *
 * Filter Field options:
 *   - field_type (o): the widget class to use to render the field
 *   - field_options (o): the options to give to the widget
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-import-type FieldDescriptionOptions from FieldDescriptionInterface
 */
abstract class BaseFieldDescription implements FieldDescriptionInterface
{
    /**
     * @var string the field name
     */
    protected $name;

    /**
     * @var string|null the type
     */
    protected $type;

    /**
     * @var string|int|null the original mapping type
     */
    protected $mappingType;

    /**
     * @var string the field name (of the form)
     */
    protected $fieldName;

    /**
     * @var array<string, mixed> association mapping
     */
    protected $associationMapping = [];

    /**
     * @var array<string, mixed> field information
     */
    protected $fieldMapping = [];

    /**
     * @var array<string, mixed> parent mapping association
     */
    protected $parentAssociationMappings = [];

    /**
     * @var string|null the template name
     */
    protected $template;

    /**
     * @var array<string, mixed> the option collection
     * @phpstan-var FieldDescriptionOptions
     */
    protected $options = [];

    /**
     * @var AdminInterface<object>|null the parent Admin instance
     */
    protected $parent;

    /**
     * @var AdminInterface<object>|null the related admin instance
     */
    protected $admin;

    /**
     * @var AdminInterface<object>|null the associated admin class if the object is associated to another entity
     */
    protected $associationAdmin;

    /**
     * @param mixed[] $fieldMapping
     * @param mixed[] $associationMapping
     * @param mixed[] $parentAssociationMappings
     *
     * @phpstan-param FieldDescriptionOptions $options
     */
    public function __construct(
        string $name,
        array $options = [],
        array $fieldMapping = [],
        array $associationMapping = [],
        array $parentAssociationMappings = [],
        ?string $fieldName = null
    ) {
        $this->setName($name);

        if (null === $fieldName) {
            $fieldName = $name;
        }

        $this->fieldName = $fieldName;

        $this->setOptions($options);

        if ([] !== $fieldMapping) {
            $this->setFieldMapping($fieldMapping);
        }

        if ([] !== $associationMapping) {
            $this->setAssociationMapping($associationMapping);
        }

        if ([] !== $parentAssociationMappings) {
            $this->setParentAssociationMappings($parentAssociationMappings);
        }
    }

    final public function getFieldName(): string
    {
        return $this->fieldName;
    }

    final public function setName(string $name): void
    {
        $this->name = $name;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    final public function setOption(string $name, $value): void
    {
        $this->options[$name] = $value;
    }

    final public function setOptions(array $options): void
    {
        // set the type if provided
        if (isset($options['type'])) {
            $this->setType($options['type']);
            unset($options['type']);
        }

        // remove property value
        if (isset($options['template'])) {
            $this->setTemplate($options['template']);
            unset($options['template']);
        }

        $this->options = $options;
    }

    final public function getOptions(): array
    {
        return $this->options;
    }

    final public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    final public function getTemplate(): ?string
    {
        return $this->template;
    }

    final public function setType(?string $type): void
    {
        $this->type = $type;
    }

    final public function getType(): ?string
    {
        return $this->type;
    }

    final public function setParent(AdminInterface $parent): void
    {
        $this->parent = $parent;
    }

    final public function getParent(): AdminInterface
    {
        if (null === $this->parent) {
            throw new \LogicException(sprintf('%s has no parent.', static::class));
        }

        return $this->parent;
    }

    final public function hasParent(): bool
    {
        return null !== $this->parent;
    }

    final public function getAssociationMapping(): array
    {
        return $this->associationMapping;
    }

    final public function getFieldMapping(): array
    {
        return $this->fieldMapping;
    }

    final public function getParentAssociationMappings(): array
    {
        return $this->parentAssociationMappings;
    }

    final public function setAssociationAdmin(AdminInterface $associationAdmin): void
    {
        $this->associationAdmin = $associationAdmin;
        $this->associationAdmin->setParentFieldDescription($this);
    }

    final public function getAssociationAdmin(): AdminInterface
    {
        if (null === $this->associationAdmin) {
            throw new \LogicException(sprintf('%s has no association admin.', static::class));
        }

        return $this->associationAdmin;
    }

    final public function hasAssociationAdmin(): bool
    {
        return null !== $this->associationAdmin;
    }

    final public function setAdmin(AdminInterface $admin): void
    {
        $this->admin = $admin;
    }

    final public function getAdmin(): AdminInterface
    {
        if (null === $this->admin) {
            throw new \LogicException(sprintf('%s has no admin.', static::class));
        }

        return $this->admin;
    }

    final public function hasAdmin(): bool
    {
        return null !== $this->admin;
    }

    final public function mergeOption(string $name, array $options = []): void
    {
        if (!isset($this->options[$name])) {
            $this->options[$name] = [];
        }

        if (!\is_array($this->options[$name])) {
            throw new \RuntimeException(sprintf('The key `%s` does not point to an array value', $name));
        }

        $this->options[$name] = array_merge($this->options[$name], $options);
    }

    final public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * @return string|false|null
     */
    final public function getLabel()
    {
        return $this->getOption('label');
    }

    final public function isSortable(): bool
    {
        return false !== $this->getOption('sortable', false);
    }

    final public function getSortFieldMapping(): array
    {
        return $this->getOption('sort_field_mapping');
    }

    final public function getSortParentAssociationMapping(): array
    {
        return $this->getOption('sort_parent_association_mappings');
    }

    final public function getTranslationDomain()
    {
        return $this->getOption('translation_domain') ?? $this->getAdmin()->getTranslationDomain();
    }

    final public function isVirtual(): bool
    {
        return false !== $this->getOption('virtual_field', false);
    }

    final public function describesAssociation(): bool
    {
        return $this->describesSingleValuedAssociation() || $this->describesCollectionValuedAssociation();
    }

    /**
     * @param array<string, mixed> $fieldMapping
     */
    abstract protected function setFieldMapping(array $fieldMapping): void;

    /**
     * @param array<string, mixed> $associationMapping
     */
    abstract protected function setAssociationMapping(array $associationMapping): void;

    /**
     *  @param array<array<string, mixed>> $parentAssociationMappings
     */
    abstract protected function setParentAssociationMappings(array $parentAssociationMappings): void;

    /**
     * @throws NoValueException
     *
     * @return mixed
     */
    final protected function getFieldValue(?object $object, string $fieldName)
    {
        if ($this->isVirtual() || null === $object) {
            return null;
        }

        $dotPos = strpos($fieldName, '.');
        if ($dotPos > 0) {
            $child = $this->getFieldValue($object, substr($fieldName, 0, $dotPos));
            if (null !== $child && !\is_object($child)) {
                throw new NoValueException(sprintf(
                    <<<'EXCEPTION'
                    Unexpected value when accessing to the property "%s" on the class "%s" for the field "%s".
                    Expected object|null, got %s.
                    EXCEPTION,
                    $fieldName,
                    \get_class($object),
                    $this->getName(),
                    \gettype($child)
                ));
            }

            return $this->getFieldValue($child, substr($fieldName, $dotPos + 1));
        }

        // prefer method name given in the code option
        $accessor = $this->getOption('accessor', $fieldName);
        if (!\is_string($accessor) && \is_callable($accessor)) {
            return $accessor($object);
        } elseif (!\is_string($accessor) && !$accessor instanceof PropertyPathInterface) {
            throw new \TypeError(sprintf(
                'The option "accessor" must be a string, a callable or a %s, %s given.',
                PropertyPathInterface::class,
                \is_object($accessor) ? 'instance of '.\get_class($accessor) : \gettype($accessor)
            ));
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableMagicCall()
            ->getPropertyAccessor();

        try {
            return $propertyAccessor->getValue($object, $accessor);
        } catch (ExceptionInterface $exception) {
            throw new NoValueException(
                sprintf('Cannot access property "%s" in class "%s".', $this->getName(), $this->getAdmin()->getClass()),
                (int) $exception->getCode(),
                $exception
            );
        }
    }
}
