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

use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\CoreBundle\Model\Adapter\AdapterInterface;
use Symfony\Component\Form\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
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
     *
     * @deprecated since 3.12, to be removed in 4.0
     * NEXT_MAJOR: remove this property
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
    public function __construct($choiceList, $modelManager, $class = null)
    {
        /*
        NEXT_MAJOR: Remove condition , magic methods, legacyConstructor() method, $choiceList property and argument
        __construct() signature should be : public function __construct(ModelManager $modelManager, $class)
         */

        $args = func_get_args();

        if (3 == func_num_args()) {
            $this->legacyConstructor($args);
        } else {
            $this->modelManager = $args[0];
            $this->class = $args[1];
        }
    }

    /**
     * @internal
     */
    public function __get($name)
    {
        if ('choiceList' === $name) {
            $this->triggerDeprecation();
        }

        return $this->$name;
    }

    /**
     * @internal
     */
    public function __set($name, $value)
    {
        if ('choiceList' === $name) {
            $this->triggerDeprecation();
        }

        $this->$name = $value;
    }

    /**
     * @internal
     */
    public function __isset($name)
    {
        if ('choiceList' === $name) {
            $this->triggerDeprecation();
        }

        return isset($this->$name);
    }

    /**
     * @internal
     */
    public function __unset($name)
    {
        if ('choiceList' === $name) {
            $this->triggerDeprecation();
        }

        unset($this->$name);
    }

    public function transform($collection)
    {
        if (null === $collection) {
            return [];
        }

        $array = [];
        foreach ($collection as $key => $entity) {
            $id = implode(AdapterInterface::ID_SEPARATOR, $this->getIdentifierValues($entity));

            $array[] = $id;
        }

        return $array;
    }

    public function reverseTransform($keys)
    {
        if (!is_array($keys)) {
            throw new UnexpectedTypeException($keys, 'array');
        }

        $collection = $this->modelManager->getModelCollectionInstance($this->class);
        $notFound = [];

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
     * Simulates the old constructor for BC.
     *
     * @param array $args
     *
     * @throws RuntimeException
     */
    private function legacyConstructor($args)
    {
        $choiceList = $args[0];

        if (!$choiceList instanceof ModelChoiceList
            && !$choiceList instanceof ModelChoiceLoader
            && !$choiceList instanceof LazyChoiceList) {
            throw new RuntimeException('First param passed to ModelsToArrayTransformer should be instance of
                ModelChoiceLoader or ModelChoiceList or LazyChoiceList');
        }

        $this->choiceList = $choiceList;
        $this->modelManager = $args[1];
        $this->class = $args[2];
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

    /**
     * @internal
     */
    private function triggerDeprecation()
    {
        @trigger_error(sprintf(
                'Using the "%s::$choiceList" property is deprecated since version 3.12 and will be removed in 4.0.',
                __CLASS__),
            E_USER_DEPRECATED)
        ;
    }
}
