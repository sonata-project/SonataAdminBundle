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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['btn_add'] = $options['btn_add'];
        $view->vars['btn_catalogue'] = $options['btn_catalogue'];
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'modifiable'    => false,
            'type'          => 'text',
            'type_options'  => array(),
            'btn_add'       => 'link_add',
            'btn_catalogue' => 'SonataAdminBundle'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_collection';
    }
}
