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

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Util\TraversableToCollection;
use Sonata\Doctrine\Adapter\AdapterInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
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
            $array[] = implode(AdapterInterface::ID_SEPARATOR, $this->getIdentifierValues($model));
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
            $result = $value;
        } else {
            $query = $this->modelManager->createQuery($this->class);
            $this->modelManager->addIdentifiersToQuery($this->class, $query, $value);
            $result = $this->modelManager->executeQuery($query);
        }

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
     * @return array<int|string>
     *
     * @phpstan-param T $model
     */
    private function getIdentifierValues(object $model): array
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
