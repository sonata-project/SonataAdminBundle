<?php

namespace Sonata\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Sonata\AdminBundle\Form\Type\BooleanType;

class BooleanToSonataBooleanTransformer implements DataTransformerInterface
{

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === true) {
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
