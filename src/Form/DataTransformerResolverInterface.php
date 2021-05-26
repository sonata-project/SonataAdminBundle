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

namespace Sonata\AdminBundle\Form;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Peter Gribanov <info@peter-gribanov.ru>
 */
interface DataTransformerResolverInterface
{
    public function addCustomGlobalTransformer(string $fieldType, DataTransformerInterface $dataTransformer): void;

    /**
     * @param ModelManagerInterface<object> $modelManager
     */
    public function resolve(
        FieldDescriptionInterface $fieldDescription,
        ModelManagerInterface $modelManager
    ): ?DataTransformerInterface;
}
