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

use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\Doctrine\Adapter\AdapterInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
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
     * @throws RuntimeException
     */
    public function __construct(ModelManagerInterface $modelManager, string $class)
    {
        $this->modelManager = $modelManager;
        $this->class = $class;
    }

    public function transform($collection)
    {
        if (null === $collection) {
            return [];
        }

        $array = [];
        foreach ($collection as $key => $model) {
            $id = implode(AdapterInterface::ID_SEPARATOR, $this->getIdentifierValues($model));

            $array[] = $id;
        }

        return $array;
    }

    public function reverseTransform($keys)
    {
        if (!\is_array($keys)) {
            throw new UnexpectedTypeException($keys, 'array');
        }

        $collection = $this->modelManager->getModelCollectionInstance($this->class);
        $notFound = [];

        // optimize this into a SELECT WHERE IN query
        foreach ($keys as $key) {
            if ($model = $this->modelManager->find($this->class, $key)) {
                $collection[] = $model;
            } else {
                $notFound[] = $key;
            }
        }

        if (\count($notFound) > 0) {
            throw new TransformationFailedException(sprintf('The entities with keys "%s" could not be found', implode('", "', $notFound)));
        }

        return $collection;
    }

    /**
     * @param object $model
     */
    private function getIdentifierValues($model): array
    {
        try {
            return $this->modelManager->getIdentifierValues($model);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('Unable to retrieve the identifier values for entity %s', ClassUtils::getClass($model)), 0, $e);
        }
    }
}
