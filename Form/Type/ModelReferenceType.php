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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;

class ModelReferenceType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->prependClientTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']));
}

    public function createBuilder($name, FormFactoryInterface $factory, array $options)
    {
        return parent::createBuilder($name, $factory, $options);
        $this->parent = $options['parent'];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(OptionsResolverInterface $resolver)
    {
        return array(
            'model_manager'     => null,
            'class'             => null,
            'parent'            => 'hidden',
        );
        $compound = function (Options $options) {
            return $options['parent'];
        };
        $resolver->setDefaults(array(
            'compound' => $compound,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_model_reference';
    }
}