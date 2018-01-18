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

use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This type defines a standard text field with autocomplete feature.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 * @author Florent Denis <dflorent.pokap@gmail.com>
 */
class ModelAutocompleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ModelToIdPropertyTransformer($options['model_manager'], $options['class'], $options['property'], $options['multiple'], $options['to_string_callback']), true);

        $builder->setAttribute('property', $options['property']);
        $builder->setAttribute('callback', $options['callback']);
        $builder->setAttribute('minimum_input_length', $options['minimum_input_length']);
        $builder->setAttribute('items_per_page', $options['items_per_page']);
        $builder->setAttribute('req_param_name_page_number', $options['req_param_name_page_number']);
        $builder->setAttribute(
            'disabled',
            $options['disabled']
            // NEXT_MAJOR: Remove this when bumping Symfony constraint to 2.8+
            || (array_key_exists('read_only', $options) && $options['read_only'])
        );
        $builder->setAttribute('to_string_callback', $options['to_string_callback']);
        $builder->setAttribute('target_admin_access_action', $options['target_admin_access_action']);

        if ($options['multiple']) {
            $resizeListener = new ResizeFormListener(HiddenType::class, [], true, true, true);

            $builder->addEventSubscriber($resizeListener);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ([
            'admin_code',
            'placeholder',
            'multiple',
            'minimum_input_length',
            'items_per_page',
            'width',
            // ajax parameters
            'url',
            'route',
            'req_params',
            'req_param_name_search',
            'req_param_name_page_number',
            'req_param_name_items_per_page',
            'quiet_millis',
            'cache',
            // CSS classes
            'container_css_class',
            'dropdown_css_class',
            'dropdown_item_css_class',
            'dropdown_auto_width',
            // template
            'template',
            'context',
            // add button
            'btn_add',
            'btn_catalogue',
            // allow HTML
            'safe_label',
        ] as $passthroughOption) {
            $view->vars[$passthroughOption] = $options[$passthroughOption];
        }
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
        $compound = function (Options $options) {
            return $options['multiple'];
        };

        $resolver->setDefaults([
            'attr' => [],
            'compound' => $compound,
            'model_manager' => null,
            'class' => null,
            'admin_code' => null,
            'callback' => null,
            'multiple' => false,
            'width' => '',
            'context' => '',

            'placeholder' => '',
            'minimum_input_length' => 3, //minimum 3 chars should be typed to load ajax data
            'items_per_page' => 10, //number of items per page
            'quiet_millis' => 100,
            'cache' => false,

            'to_string_callback' => null,

            // add button
            // NEXT_MAJOR: Set this value to 'link_add' to display button by default
            'btn_add' => false,
            'btn_catalogue' => 'SonataAdminBundle',

            // ajax parameters
            'url' => '',
            'route' => ['name' => 'sonata_admin_retrieve_autocomplete_items', 'parameters' => []],
            'req_params' => [],
            'req_param_name_search' => 'q',
            'req_param_name_page_number' => '_page',
            'req_param_name_items_per_page' => '_per_page',

            // security
            'target_admin_access_action' => 'list',

            // CSS classes
            'container_css_class' => '',
            'dropdown_css_class' => '',
            'dropdown_item_css_class' => '',

            'dropdown_auto_width' => false,

            // allow HTML
            'safe_label' => false,

            'template' => '@SonataAdmin/Form/Type/sonata_type_model_autocomplete.html.twig',
        ]);

        $resolver->setRequired(['property']);
    }

    public function getBlockPrefix()
    {
        return 'sonata_type_model_autocomplete';
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
}
