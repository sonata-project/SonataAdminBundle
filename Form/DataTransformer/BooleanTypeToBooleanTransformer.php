<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Sonata\AdminBundle\Form\Type\BooleanType;

class BooleanTypeToBooleanTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === true or (int)$value === BooleanType::TYPE_YES) {
            return BooleanType::TYPE_YES;
        }

        return BooleanType::TYPE_NO;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value === BooleanType::TYPE_YES) {
            return true;
        }

        return false;
    }
}
