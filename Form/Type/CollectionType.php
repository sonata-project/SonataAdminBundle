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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Sonata\AdminBundle\Form\EventListener\ResizeFormListener;

class CollectionType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $assocationMapping = $options['sonata_field_description']->getAssociationMapping();

        $listener = new ResizeFormListener(
            $builder->getFormFactory(),
            $options['type'],
            $options['type_options'],
            $options['modifiable'],
            $assocationMapping
        );

        $builder->addEventSubscriber(
            new \Sonata\AdminBundle\Form\EventListener\MergeCollectionListener(
                $options['model_manager'],
                $assocationMapping
            )
        );
        $builder->addEventSubscriber($listener);
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'model_manager' => null,
            'modifiable'    => false,
            'type'          => 'text',
            'type_options'  => array()
        );
    }

    public function getName()
    {
        return 'sonata_type_collection';
    }
}