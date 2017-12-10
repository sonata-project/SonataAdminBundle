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

use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This type can be used to select one associated model from a list.
 *
 * The associated model must be in a single-valued association relationship (e.g many-to-one)
 * with the model currently edited in the parent form.
 * The associated model must have an admin class registered.
 *
 * The selected model's identifier is rendered in an hidden input.
 *
 * When a model is selected, a short description is displayed by the widget.
 * This description can be customized by overriding the associated admin's
 * `short_object_description` template and/or overriding it's `toString` method.
 *
 * The widget also provides three action buttons:
 *  - a button to open the associated admin list view in a dialog,
 *    in order to select an associated model.
 *  - a button to open the associated admin create form in a dialog,
 *    in order to create and select an associated model.
 *  - a button to unlink the associated model, if any.
 */
class ModelListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->resetViewTransformers()
            ->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->vars['sonata_admin'])) {
            // set the correct edit mode
            $view->vars['sonata_admin']['edit'] = 'list';
        }
        $view->vars['btn_add'] = $options['btn_add'];
        $view->vars['btn_edit'] = $options['btn_edit'];
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
        $resolver->setDefaults([
            'model_manager' => null,
            'class' => null,
            'btn_add' => 'link_add',
            'btn_edit' => 'link_edit',
            'btn_list' => 'link_list',
            'btn_delete' => 'link_delete',
            'btn_catalogue' => 'SonataAdminBundle',
        ]);
    }

    public function getParent()
    {
        return TextType::class;
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
        return 'sonata_type_model_list';
    }
}
