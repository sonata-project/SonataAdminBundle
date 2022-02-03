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
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 * @phpstan-implements DataTransformerInterface<\Traversable<T>, array<int|string>>
 */
final class ModelsToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     * @phpstan-var ModelManagerInterface<T>
     */
    private $modelManager;

    /**
     * @var string
     *
     * @phpstan-var class-string<T>
     */
    private $class;

    /**
     * @phpstan-param ModelManagerInterface<T> $modelManager
     * @phpstan-param class-string<T>          $class
     */
    public function __construct(ModelManagerInterface $modelManager, string $class)
    {
        $this->modelManager = $modelManager;
        $this->class = $class;
    }

    /**
     * @param \Traversable<object>|null $value
     *
     * @return string[]
     *
     * @phpstan-param \Traversable<T>|null $value
     */
    public function transform($value): array
    {
        if (null === $value) {
            return [];
        }

        $array = [];
        foreach ($value as $model) {
            $identifier = $this->modelManager->getNormalizedIdentifier($model);
            if (null === $identifier) {
                throw new TransformationFailedException(sprintf(
                    'No identifier was found for the model "%s".',
                    ClassUtils::getClass($model)
                ));
            }

            $array[] = $identifier;
        }

        return $array;
    }

    /**
     * @param array<int|string>|null $value
     *
     * @throws UnexpectedTypeException
     *
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
            return new ArrayCollection();
        }

        $query = $this->modelManager->createQuery($this->class);
        $this->modelManager->addIdentifiersToQuery($this->class, $query, $value);
        $queryResult = $this->modelManager->executeQuery($query);

        $modelsById = [];
        foreach ($queryResult as $model) {
            $identifier = $this->modelManager->getNormalizedIdentifier($model);
            if (null === $identifier) {
                throw new TransformationFailedException(sprintf(
                    'No identifier was found for the model "%s".',
                    ClassUtils::getClass($model)
                ));
            }

            $modelsById[$identifier] = $model;
        }

        $result = [];
        foreach ($value as $identifier) {
            if (!isset($modelsById[$identifier])) {
                throw new TransformationFailedException(sprintf(
                    'No model was found for the identifier "%s".',
                    $identifier,
                ));
            }

            $result[] = $modelsById[$identifier];
        }

        return new ArrayCollection($result);
    }
}
