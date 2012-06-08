<?php

namespace Sonata\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Sonata\AdminBundle\Form\Type\BooleanType;

class BooleanTransformer implements DataTransformerInterface{
    public function transform($boolFromPHP)
    {
        return ($boolFromPHP)?BooleanType::TYPE_YES:BooleanType::TYPE_NO;
    }

    public function reverseTransform($boolFromSonata){
        return $boolFromSonata == BooleanType::TYPE_YES;
    }
}
