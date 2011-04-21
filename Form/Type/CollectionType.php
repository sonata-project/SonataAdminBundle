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

namespace Sonata\AdminBundle\Form\Type;

use Sonata\AdminBundle\Form\Type\AbstractType;
use Sonata\AdminBundle\Form\EventListener\ResizeFormListener;
use Symfony\Component\Form\FormBuilder;

class CollectionType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $listener = new ResizeFormListener($builder->getFormFactory(), $options['prototype'], true);

        $builder->addEventSubscriber($listener);
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'prototype' => null,
            'min'       => false,
            'max'       => false,
        );
    }

    public function getName()
    {
        return 'sonata_admin_collection';
    }
}