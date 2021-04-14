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

namespace Sonata\AdminBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Util\TraversableToCollection;
use Sonata\Doctrine\Adapter\AdapterInterface;
use Symfony\Component\Form\ChoiceList\LazyChoiceList;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 */
class ModelsToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     * @phpstan-var ModelManagerInterface<T>
     */
    protected $modelManager;

    /**
     * @var string
     *
     * @phpstan-var class-string<T>
     */
    protected $class;

    /**
     * @var ModelChoiceLoader|LazyChoiceList
     *
     * @deprecated since sonata-project/admin-bundle 3.12, to be removed in 4.0
     * NEXT_MAJOR: remove this property
     */
    protected $choiceList;

    /**
     * @param LazyChoiceList|ModelChoiceLoader $choiceList
     * @param ModelManagerInterface            $modelManager
     * @param string                           $class
     *
     * @phpstan-param ModelManagerInterface<T> $modelManager
     * @phpstan-param class-string<T>          $class
     *
     * @throws RuntimeException
     */
    public function __construct($choiceList, $modelManager, $class = null)
    {
        /*
        NEXT_MAJOR: Remove condition , magic methods, legacyConstructor() method, $choiceList property and argument
        __construct() signature should be : public function __construct(ModelManager $modelManager, string $class)
         */

        $args = \func_get_args();

        if (3 === \func_num_args()) {
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

    /**
     * @param object[]|null $value
     *
     * @return string[]
     *
     * @phpstan-param T[]|null $value
     */
    public function transform($value)
    {
        if (null === $value) {
            return [];
        }

        $array = [];
        foreach ($value as $model) {
            $id = implode(AdapterInterface::ID_SEPARATOR, $this->getIdentifierValues($model));

            $array[] = $id;
        }

        return $array;
    }

    /**
     * @return Collection<int|string, object>|null
     *
     * @phpstan-return Collection<array-key, T>|null
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!\is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if ([] === $value) {
            $result = $value;
        } else {
            $value = array_map('strval', $value);
            $query = $this->modelManager->createQuery($this->class);
            $this->modelManager->addIdentifiersToQuery($this->class, $query, $value);
            $result = $this->modelManager->executeQuery($query);
        }

        /** @phpstan-var ArrayCollection<array-key, T> $collection */
        $collection = TraversableToCollection::transform($result);

        $diffCount = \count($value) - $collection->count();

        if (0 !== $diffCount) {
            throw new TransformationFailedException(sprintf(
                '%u keys could not be found in the provided values: "%s".',
                $diffCount,
                implode('", "', $value)
            ));
        }

        return $collection;
    }

    /**
     * Simulates the old constructor for BC.
     *
     * @throws RuntimeException
     */
    private function legacyConstructor(array $args): void
    {
        $choiceList = $args[0];

        if (!$choiceList instanceof ModelChoiceLoader
            && !$choiceList instanceof LazyChoiceList) {
            throw new RuntimeException(
                'First param passed to ModelsToArrayTransformer'
                .' should be instance of ModelChoiceLoader or LazyChoiceList'
            );
        }

        $this->choiceList = $choiceList;
        $this->modelManager = $args[1];
        $this->class = $args[2];
    }

    /**
     * @param object $model
     *
     * @return mixed[]
     *
     * @phpstan-param T $model
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

    /**
     * @internal
     */
    private function triggerDeprecation(): void
    {
        @trigger_error(sprintf(
            'Using the "%s::$choiceList" property is deprecated since version 3.12 and will be removed in 4.0.',
            __CLASS__
        ), \E_USER_DEPRECATED);
    }
}
