<?php

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
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Class ModelChoiceLoader.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    private $modelManager;

    /**
     * @var string
     */
    private $class;

    private $property;

    private $query;

    private $choices;

    private $propertyPath;

    private $choiceList;

    /**
     * @param ModelManagerInterface $modelManager
     * @param string                $class
     * @param null                  $property
     * @param null                  $query
     * @param array                 $choices
     */
    public function __construct(ModelManagerInterface $modelManager, $class, $property = null, $query = null, $choices = array())
    {
        $this->modelManager = $modelManager;
        $this->class = $class;
        $this->property = $property;
        $this->query = $query;
        $this->choices = $choices;

        $this->identifier     = $this->modelManager->getIdentifierFieldNames($this->class);

        // The property option defines, which property (path) is used for
        // displaying entities as strings
        if ($property) {
            $this->propertyPath = new PropertyPath($property);
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        if (!$this->choiceList) {
            if (is_array($this->choices)) {
                $entities = $this->choices;
            } elseif ($this->query) {
                $entities = $this->modelManager->executeQuery($this->query);
            } else {
                $entities = $this->modelManager->findBy($this->class);
            }

            $choices = array();
            foreach ($entities as $key => $entity) {
                if ($this->propertyPath) {
                    // If the property option was given, use it
                    $propertyAccessor = PropertyAccess::createPropertyAccessor();
                    $valueObject = $propertyAccessor->getValue($entity, $this->propertyPath);
                } else {
                    // Otherwise expect a __toString() method in the entity
                    try {
                        $valueObject = (string) $entity;
                    } catch (\Exception $e) {
                        throw new RuntimeException(sprintf("Unable to convert the entity %s to String, entity must have a '__toString()' method defined", ClassUtils::getClass($entity)), 0, $e);
                    }
                }

                $id = implode('~', $this->getIdentifierValues($entity));

                if (!array_keys($choices, $valueObject)) {
                    $choices[$valueObject] = array();
                }

                $choices[$valueObject][] = $id;
            }

            $finalChoices = array();
            foreach ($choices as $valueObject => $idx) {
                if (count($idx) > 1) { // avoid issue with identical values ...
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

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $choices, $value = null)
    {
        throw new \RuntimeException('Not implemented, please send us your usecase');
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        throw new \RuntimeException('Not implemented, please send us your usecase');
    }

    /**
     * @param object $entity
     *
     * @return array
     */
    private function getIdentifierValues($entity)
    {
        try {
            return $this->modelManager->getIdentifierValues($entity);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to retrieve the identifier values for entity %s', ClassUtils::getClass($entity)), 0, $e);
        }
    }
}
