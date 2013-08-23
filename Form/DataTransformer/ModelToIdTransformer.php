<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelToIdTransformer implements DataTransformerInterface
{
    protected $modelManager;

    protected $className;

    /**
     * @param ModelManagerInterface $modelManager
     * @param string                $className
     */
    public function __construct(ModelManagerInterface $modelManager, $className)
    {
        $this->modelManager = $modelManager;
        $this->className    = $className;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($newId)
    {
        if (!isset($newId) || strlen($newId) === 0) {
            return null;
        }

        return $this->modelManager->find($this->className, $newId);
    }

    /**
     * {@inheritDoc}
     */
    public function transform($entity)
    {
        if (empty($entity)) {
            return null;
        }

        return current($this->modelManager->getIdentifierValues($entity));
    }
}
