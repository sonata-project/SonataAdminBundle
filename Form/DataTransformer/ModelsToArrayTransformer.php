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
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Class ModelsToArrayTransformer.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelsToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ModelChoiceList
     */
    protected $choiceList;

    /**
     * ModelsToArrayTransformer constructor.
     *
     * @param ModelChoiceList|LazyChoiceList|ModelChoiceLoader $choiceList
     * @param ModelManagerInterface                            $modelManager
     * @param $class
     *
     * @throws RuntimeException
     */
    public function __construct($choiceList, ModelManagerInterface $modelManager, $class)
    {
        if (!$choiceList instanceof ModelChoiceList
            && !$choiceList instanceof ModelChoiceLoader
            && !$choiceList instanceof LazyChoiceList) {
            throw new RuntimeException('First param passed to ModelsToArrayTransformer should be instance of
                ModelChoiceLoader or ModelChoiceList or LazyChoiceList');
        }

        $this->choiceList = $choiceList;
        $this->modelManager = $modelManager;
        $this->class = $class;
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
        foreach ($collection as $key => $entity) {
            $id = implode('~', $this->getIdentifierValues($entity));

            $array[] = $id;
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($keys)
    {
        if (!is_array($keys)) {
            throw new UnexpectedTypeException($keys, 'array');
        }

        $collection = $this->modelManager->getModelCollectionInstance($this->class);
        $notFound = array();

        // optimize this into a SELECT WHERE IN query
        foreach ($keys as $key) {
            if ($entity = $this->modelManager->find($this->class, $key)) {
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
