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

namespace Sonata\AdminBundle\Form\ChoiceList;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ModelChoiceLoader implements ChoiceLoaderInterface
{
    private ?ChoiceListInterface $choiceList = null;

    /**
     * @param ModelManagerInterface<object> $modelManager
     * @param object[]|null                 $choices
     *
     * @phpstan-param class-string $class
     */
    public function __construct(
        private ModelManagerInterface $modelManager,
        private PropertyAccessorInterface $propertyAccessor,
        private string $class,
        private ?string $property = null,
        private ?object $query = null,
        private ?array $choices = null,
    ) {
        if (null !== $query && !$this->modelManager->supportsQuery($query)) {
            throw new InvalidArgumentException('The model manager does not support the query.');
        }
    }

    public function loadChoiceList($value = null): ChoiceListInterface
    {
        if (null === $this->choiceList) {
            if (null !== $this->query) {
                $entities = $this->modelManager->executeQuery($this->query);
            } elseif (\is_array($this->choices)) {
                $entities = $this->choices;
            } else {
                $entities = $this->modelManager->findBy($this->class);
            }

            $choices = [];
            foreach ($entities as $model) {
                if (null !== $this->property) {
                    // If the property option was given, use it
                    $valueObject = $this->propertyAccessor->getValue($model, $this->property);
                } elseif (method_exists($model, '__toString')) {
                    // Otherwise expect a __toString() method in the entity
                    $valueObject = (string) $model;
                } else {
                    throw new TransformationFailedException(\sprintf(
                        'Unable to convert the model "%s" to string, provide "property" option'
                        .' or implement "__toString()" method in your model.',
                        $this->class
                    ));
                }

                if (!\array_key_exists($valueObject, $choices)) {
                    $choices[$valueObject] = [];
                }

                $identifier = $this->modelManager->getNormalizedIdentifier($model);
                if (null === $identifier) {
                    throw new TransformationFailedException(\sprintf(
                        'No identifier was found for the model "%s".',
                        $this->class
                    ));
                }

                $choices[$valueObject][] = $identifier;
            }

            $finalChoices = [];
            foreach ($choices as $valueObject => $idx) {
                if (\count($idx) > 1) { // avoid issue with identical values ...
                    foreach ($idx as $id) {
                        $finalChoices[\sprintf('%s (id: %s)', $valueObject, $id)] = $id;
                    }
                } else {
                    $finalChoices[$valueObject] = current($idx);
                }
            }

            $this->choiceList = new ArrayChoiceList($finalChoices, $value);
        }

        return $this->choiceList;
    }

    public function loadChoicesForValues(array $values, $value = null): array
    {
        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    public function loadValuesForChoices(array $choices, $value = null): array
    {
        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}
