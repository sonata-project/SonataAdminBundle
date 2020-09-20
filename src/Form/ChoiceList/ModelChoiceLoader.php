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

use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\Doctrine\Adapter\AdapterInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelChoiceLoader implements ChoiceLoaderInterface
{
    public $identifier;

    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    /**
     * @var string
     *
     * @phpstan-var class-string
     */
    private $class;

    /**
     * @var string|null
     */
    private $property;

    /**
     * @var object|null
     */
    private $query;

    /**
     * @var object[]
     */
    private $choices;

    /**
     * @var PropertyPath|null
     */
    private $propertyPath;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var ChoiceListInterface|null
     */
    private $choiceList;

    /**
     * @param string      $class
     * @param string|null $property
     * @param object|null $query
     * @param object[]    $choices
     *
     * @phpstan-param class-string $class
     */
    public function __construct(
        ModelManagerInterface $modelManager,
        $class,
        $property = null,
        $query = null,
        $choices = [],
        ?PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->modelManager = $modelManager;
        $this->class = $class;
        $this->property = $property;
        $this->choices = $choices;

        if ($query) {
            // NEXT_MAJOR: Remove the method_exists check.
            if (method_exists($this->modelManager, 'supportsQuery')) {
                if (!$this->modelManager->supportsQuery($query)) {
                    // NEXT_MAJOR: Remove the deprecation and uncomment the exception.
                    @trigger_error(
                        'Passing a query which is not supported by the model manager is deprecated since'
                        .' sonata-project/admin-bundle 3.76 and will throw an exception in version 4.0.',
                        E_USER_DEPRECATED
                    );
                    // throw new \InvalidArgumentException('The model manager does not support the query.');
                }
            }

            $this->query = $query;
        }

        $this->identifier = $this->modelManager->getIdentifierFieldNames($this->class);

        // The property option defines, which property (path) is used for
        // displaying entities as strings
        if ($property) {
            $this->propertyPath = new PropertyPath($property);
            $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
        }
    }

    public function loadChoiceList($value = null)
    {
        if (!$this->choiceList) {
            if ($this->query) {
                $entities = $this->modelManager->executeQuery($this->query);
            } elseif (\is_array($this->choices) && \count($this->choices) > 0) {
                $entities = $this->choices;
            } else {
                $entities = $this->modelManager->findBy($this->class);
            }

            $choices = [];
            foreach ($entities as $model) {
                if ($this->propertyPath) {
                    // If the property option was given, use it
                    $valueObject = $this->propertyAccessor->getValue($model, $this->propertyPath);
                } else {
                    // Otherwise expect a __toString() method in the entity
                    try {
                        $valueObject = (string) $model;
                    } catch (\Exception $e) {
                        throw new RuntimeException(sprintf(
                            'Unable to convert the entity "%s" to string, provide "property" option'
                            .' or implement "__toString()" method in your entity.',
                            ClassUtils::getClass($model)
                        ), 0, $e);
                    }
                }

                $id = implode(AdapterInterface::ID_SEPARATOR, $this->getIdentifierValues($model));

                if (!\array_key_exists($valueObject, $choices)) {
                    $choices[$valueObject] = [];
                }

                $choices[$valueObject][] = $id;
            }

            $finalChoices = [];
            foreach ($choices as $valueObject => $idx) {
                if (\count($idx) > 1) { // avoid issue with identical values ...
                    foreach ($idx as $id) {
                        $finalChoices[sprintf('%s (id: %s)', $valueObject, $id)] = $id;
                    }
                } else {
                    $finalChoices[$valueObject] = current($idx);
                }
            }

            $this->choiceList = new ArrayChoiceList($finalChoices, $value);
        }

        return $this->choiceList;
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    public function loadValuesForChoices(array $choices, $value = null)
    {
        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }

    /**
     * @param object $model
     *
     * @return mixed[]
     */
    private function getIdentifierValues($model): array
    {
        try {
            return $this->modelManager->getIdentifierValues($model);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to retrieve the identifier values for entity %s',
                ClassUtils::getClass($model)
            ), 0, $e);
        }
    }
}
