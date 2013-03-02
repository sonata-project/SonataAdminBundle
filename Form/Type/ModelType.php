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

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;

/**
 * This type define a standard select input with a + sign to add new associated object
 *
 */
class ModelType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeCollectionListener($options['model_manager']))
                ->addViewTransformer(new ModelsToArrayTransformer($options['choice_list']), true);
        } else {
            $builder
                ->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']), true)
            ;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound'          => function (Options $options) {
                return isset($options['multiple']) ? $options['multiple'] : false;
            },

            'template'          => 'choice',
            'multiple'          => false,
            'expanded'          => false,
            'model_manager'     => null,
            'class'             => null,
            'property'          => null,
            'query'             => null,
            'choices'           => null,
            'preferred_choices' => array(),
            'choice_list'       => function (Options $options, $previousValue) {
                if ($previousValue instanceof ChoiceListInterface && count($choices = $previousValue->getChoices())) {
                    return $choices;
                }

                return new ModelChoiceList(
                    $options['model_manager'],
                    $options['class'],
                    $options['property'],
                    $options['query'],
                    $options['choices']
                );
            }
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_model';
    }
}
