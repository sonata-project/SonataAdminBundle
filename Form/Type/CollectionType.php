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
use Symfony\Component\Form\FormBuilderInterface;

use Sonata\AdminBundle\Form\EventListener\ResizeFormListener;

class CollectionType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $listener = new ResizeFormListener(
            $builder->getFormFactory(),
            $options['type'],
            $options['type_options'],
            $options['modifiable']
        );

        $builder->addEventSubscriber($listener);
    }

    public function getDefaultOptions()
    {
        return array(
            'modifiable'    => false,
            'type'          => 'text',
            'type_options'  => array()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_collection';
    }
}