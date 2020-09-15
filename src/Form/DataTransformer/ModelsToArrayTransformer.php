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
     */
    private $modelManager;

    /**
     * @var string
     *
     * @phpstan-var class-string<T>
     */
    private $class;

    /**
     * @phpstan-param class-string<T> $class
     */
    public function __construct(ModelManagerInterface $modelManager, string $class)
    {
        $this->modelManager = $modelManager;
        $this->class = $class;
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

        /** @phpstan-var ArrayCollection<array-key, T> $collection */
        $collection = new ArrayCollection();
        $notFound = [];

        // optimize this into a SELECT WHERE IN query
        foreach ($value as $key) {
            if ($model = $this->modelManager->find($this->class, $key)) {
                $collection->add($model);
            } else {
                $notFound[] = $key;
            }
        }

        if (\count($notFound) > 0) {
            throw new TransformationFailedException(sprintf(
                'The entities with keys "%s" could not be found',
                implode('", "', $notFound)
            ));
        }

        return $collection;
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
}
