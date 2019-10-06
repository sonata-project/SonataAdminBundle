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

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct(ModelManagerInterface $modelManager, $className)
    {
        $this->modelManager = $modelManager;
        $this->className = $className;
    }

    public function reverseTransform($newId)
    {
        if (empty($newId) && !\in_array($newId, ['0', 0], true)) {
            return null;
        }

        return $this->modelManager->find($this->className, $newId);
    }

    public function transform($entity)
    {
        if (empty($entity)) {
            return null;
        }

        return $this->modelManager->getNormalizedIdentifier($entity);
    }
}
