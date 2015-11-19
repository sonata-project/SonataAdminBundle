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

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ModelToIdTransformer.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
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
     * @param ModelManagerInterface $modelManager
     * @param string                $className
     */
    public function __construct(ModelManagerInterface $modelManager, $className)
    {
        $this->modelManager = $modelManager;
        $this->className    = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($newId)
    {
        if (empty($newId) && !in_array($newId, array('0', 0), true)) {
            return;
        }

        return $this->modelManager->find($this->className, $newId);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($entity)
    {
        if (empty($entity)) {
            return;
        }

        return $this->modelManager->getNormalizedIdentifier($entity);
    }
}
