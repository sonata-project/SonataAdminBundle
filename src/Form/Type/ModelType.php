<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Type;

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * This type define a standard select input with a + sign to add new associated object.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ModelType extends AbstractType
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder->addViewTransformer(
                new ModelsToArrayTransformer($options['model_manager'], $options['class']),
                true
            );

            $builder
                ->addEventSubscriber(new MergeCollectionListener($options['model_manager']))
            ;
        } else {
            $builder
                ->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']), true)
            ;
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['btn_add'] = $options['btn_add'];
        $view->vars['btn_list'] = $options['btn_list'];
        $view->vars['btn_delete'] = $options['btn_delete'];
        $view->vars['btn_catalogue'] = $options['btn_catalogue'];
    }

    /**
     * NEXT_MAJOR: Remove method, when bumping requirements to SF 2.7+.
     *
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $options = [];
        $propertyAccessor = $this->propertyAccessor;
        $options['choice_loader'] = function (Options $options, $previousValue) use ($propertyAccessor) {
            if ($previousValue && \count($choices = $previousValue->getChoices())) {
                return $choices;
            }

            return new ModelChoiceLoader(
                $options['model_manager'],
                $options['class'],
                $options['property'],
                $options['query'],
                $options['choices'],
                $propertyAccessor
            );
        };
        // NEXT_MAJOR: Remove this when dropping support for SF 2.8
        if (method_exists(FormTypeInterface::class, 'setDefaultOptions')) {
            $options['choices_as_values'] = true;
        }

        $resolver->setDefaults(array_merge($options, [
            'compound' => function (Options $options) {
                if (isset($options['multiple']) && $options['multiple']) {
                    if (isset($options['expanded']) && $options['expanded']) {
                        //checkboxes
                        return true;
                    }

                    //select tag (with multiple attribute)
                    return false;
                }

                if (isset($options['expanded']) && $options['expanded']) {
                    //radio buttons
                    return true;
                }

                //select tag
                return false;
            },

            'template' => 'choice',
            'multiple' => false,
            'expanded' => false,
            'model_manager' => null,
            'class' => null,
            'property' => null,
            'query' => null,
            'choices' => [],
            'preferred_choices' => [],
            'btn_add' => 'link_add',
            'btn_list' => 'link_list',
            'btn_delete' => 'link_delete',
            'btn_catalogue' => 'SonataAdminBundle',
        ]));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * NEXT_MAJOR: Remove when dropping Symfony <2.8 support.
     *
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'sonata_type_model';
    }
}
