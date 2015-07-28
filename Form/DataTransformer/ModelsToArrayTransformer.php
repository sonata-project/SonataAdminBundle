<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\DataTransformer;

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Symfony\Component\Form\ChoiceList\LegacyChoiceListAdapter;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ModelsToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var ModelChoiceList
     */
    protected $choiceList;

    /**
     * @param ModelChoiceList|LegacyChoiceListAdapter $choiceList
     */
    public function __construct($choiceList)
    {
        if ($choiceList instanceof LegacyChoiceListAdapter && $choiceList->getAdaptedList() instanceof ModelChoiceList) {
            $this->choiceList = $choiceList->getAdaptedList();
        } elseif ($choiceList instanceof ModelChoiceList) {
            $this->choiceList = $choiceList;
        } else {
            new \InvalidArgumentException('Argument 1 passed to '.__CLASS__.'::'.__METHOD__.' must be an instance of Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList, instance of '.get_class($choiceList).' given');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transform($collection)
    {
        if (null === $collection) {
            return array();
        }

        $array = array();

        if (count($this->choiceList->getIdentifier()) > 1) {
            // load all choices
            $availableEntities = $this->choiceList->getEntities();

            foreach ($collection as $entity) {
                // identify choices by their collection key
                $key = array_search($entity, $availableEntities);
                $array[] = $key;
            }
        } else {
            foreach ($collection as $entity) {
                $array[] = current($this->choiceList->getIdentifierValues($entity));
            }
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($keys)
    {
        $collection = $this->choiceList->getModelManager()->getModelCollectionInstance(
            $this->choiceList->getClass()
        );

        if (!$collection instanceof \ArrayAccess) {
            throw new UnexpectedTypeException($collection, '\ArrayAccess');
        }

        if ('' === $keys || null === $keys) {
            return $collection;
        }

        if (!is_array($keys)) {
            throw new UnexpectedTypeException($keys, 'array');
        }

        $notFound = array();

        // optimize this into a SELECT WHERE IN query
        foreach ($keys as $key) {
            if ($entity = $this->choiceList->getEntity($key)) {
                $collection[] = $entity;
            } else {
                $notFound[] = $key;
            }
        }

        if (count($notFound) > 0) {
            throw new TransformationFailedException(sprintf('The entities with keys "%s" could not be found', implode('", "', $notFound)));
        }

        return $collection;
    }
}
