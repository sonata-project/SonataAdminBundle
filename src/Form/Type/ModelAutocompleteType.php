<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Form\Type;

use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This type defines a standard text field with autocomplete feature.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 * @author Florent Denis <dflorent.pokap@gmail.com>
 */
final class ModelAutocompleteType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
        );
        $builder->setAttribute('to_string_callback', $options['to_string_callback']);
        $builder->setAttribute('target_admin_access_action', $options['target_admin_access_action']);

        if (true === $options['multiple']) {
            $resizeListener = new ResizeFormListener(HiddenType::class, [], true, true, true);

            $builder->addEventSubscriber($resizeListener);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
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
            'property',
        ] as $passthroughOption) {
            $view->vars[$passthroughOption] = $options[$passthroughOption];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $compound = static function (Options $options): bool {
            return $options['multiple'];
        };

        $resolver->setDefaults([
            'attr' => [],
            'compound' => $compound,
            'error_bubbling' => false,
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
            'response_item_callback' => null,

            // add button
            'btn_add' => 'link_add',
            'btn_catalogue' => 'SonataAdminBundle',

            // ajax parameters
            'url' => '',
            'route' => ['name' => 'sonata_admin_retrieve_autocomplete_items', 'parameters' => []],
            'req_params' => [],
            'req_param_name_search' => 'q',
            'req_param_name_page_number' => DatagridInterface::PAGE,
            'req_param_name_items_per_page' => DatagridInterface::PER_PAGE,

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

        $resolver->setRequired(['property', 'model_manager', 'class']);
        $resolver->setAllowedTypes('model_manager', ModelManagerInterface::class);
        $resolver->setAllowedTypes('class', 'string');
        $resolver->setAllowedTypes('property', ['string', 'array']);
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_type_model_autocomplete';
    }
}
