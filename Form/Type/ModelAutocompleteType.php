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
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ModelToIdPropertyTransformer($options['model_manager'], $options['class'], $options['property'], $options['multiple'], $options['to_string_callback']), true);

        $builder->setAttribute('property', $options['property']);
        $builder->setAttribute('callback', $options['callback']);
        $builder->setAttribute('minimum_input_length', $options['minimum_input_length']);
        $builder->setAttribute('items_per_page', $options['items_per_page']);
        $builder->setAttribute('req_param_name_page_number', $options['req_param_name_page_number']);
        $builder->setAttribute('disabled', $options['disabled'] || $options['read_only']);
        $builder->setAttribute('to_string_callback', $options['to_string_callback']);

        if ($options['multiple']) {
            $resizeListener = new ResizeFormListener(
                'hidden', array(), true, true, true
            );

            $builder->addEventSubscriber($resizeListener);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['admin_code'] = $options['admin_code'];

        $view->vars['placeholder'] = $options['placeholder'];
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['minimum_input_length'] = $options['minimum_input_length'];
        $view->vars['items_per_page'] = $options['items_per_page'];
        $view->vars['width'] = $options['width'];
        $view->vars['read_only'] = $options['read_only'];

        // ajax parameters
        $view->vars['url'] = $options['url'];
        $view->vars['route'] = $options['route'];
        $view->vars['req_params'] = $options['req_params'];
        $view->vars['req_param_name_search'] = $options['req_param_name_search'];
        $view->vars['req_param_name_page_number'] = $options['req_param_name_page_number'];
        $view->vars['req_param_name_items_per_page'] = $options['req_param_name_items_per_page'];
        $view->vars['quiet_millis'] = $options['quiet_millis'];
        $view->vars['cache'] = $options['cache'];

        // CSS classes
        $view->vars['container_css_class'] = $options['container_css_class'];
        $view->vars['dropdown_css_class'] = $options['dropdown_css_class'];
        $view->vars['dropdown_item_css_class'] = $options['dropdown_item_css_class'];

        $view->vars['dropdown_auto_width'] = $options['dropdown_auto_width'];

        // template
        $view->vars['template'] = $options['template'];

        $view->vars['context'] = $options['context'];
    }

    /**
     * {@inheritdoc}
     *
     * @todo Remove it when bumping requirements to SF 2.7+
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $compound = function (Options $options) {
            return $options['multiple'];
        };

        $resolver->setDefaults(array(
            'attr'                          => array(),
            'compound'                      => $compound,
            'model_manager'                 => null,
            'class'                         => null,
            'admin_code'                    => null,
            'callback'                      => null,
            'multiple'                      => false,
            'width'                         => '',
            'context'                       => '',
            'read_only'                     => false,

            'placeholder'                   => '',
            'minimum_input_length'          => 3, //minimum 3 chars should be typed to load ajax data
            'items_per_page'                => 10, //number of items per page
            'quiet_millis'                  => 100,
            'cache'                         => false,

            'to_string_callback'            => null,

            // ajax parameters
            'url'                           => '',
            'route'                         => array('name' => 'sonata_admin_retrieve_autocomplete_items', 'parameters' => array()),
            'req_params'                    => array(),
            'req_param_name_search'         => 'q',
            'req_param_name_page_number'    => '_page',
            'req_param_name_items_per_page' => '_per_page',

            // CSS classes
            'container_css_class'           => '',
            'dropdown_css_class'            => '',
            'dropdown_item_css_class'       => '',

            'dropdown_auto_width'           => false,

            'template'                      => 'SonataAdminBundle:Form/Type:sonata_type_model_autocomplete.html.twig',
        ));

        $resolver->setRequired(array('property'));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_type_model_autocomplete';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
