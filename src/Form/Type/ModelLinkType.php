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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
class ModelLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $admin = clone $this->getAdmin($options);

        if ($admin->hasParentFieldDescription()) {
            $admin->getParentFieldDescription()->setAssociationAdmin($admin);
        }

        // hack to make sure the subject is correctly set
        if ($admin->getSubject() !== null) {
            $builder->setData($admin->getSubject());
        }
        if (isset($options['property_path']) && null === $builder->getData()) {
            $p = new PropertyAccessor(false, true);

            try {
                $parentSubject = $admin->getParentFieldDescription()->getAdmin()->getSubject();
                if (null !== $parentSubject && false !== $parentSubject) {
                    // this check is to work around duplication issue in property path
                    // https://github.com/sonata-project/SonataAdminBundle/issues/4425
                    if ($this->getFieldDescription($options)->getFieldName() === $options['property_path']) {
                        $path = $options['property_path'];
                    } else {
                        $path = $this->getFieldDescription($options)->getFieldName().$options['property_path'];
                    }

                    $subject = $p->getValue($parentSubject, $path);
                    $builder->setData($subject);
                }
            } catch (NoSuchIndexException $e) {
                // no object here
            }
            $admin->setSubject($builder->getData());
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->vars['sonata_admin'])) {
            // set the correct edit mode
            $view->vars['sonata_admin']['edit'] = 'list';
        }
        $view->vars['btn_edit'] = $options['btn_edit'];
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
            'mapped' => false,
            'model_manager' => null,
            'class' => null,
            'btn_edit' => 'link_edit',
            'btn_catalogue' => 'SonataAdminBundle',
        ]);
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
        return 'sonata_type_model_link';
    }

    /**
     * @throws \RuntimeException
     *
     * @return FieldDescriptionInterface
     */
    protected function getFieldDescription(array $options)
    {
        if (!isset($options['sonata_field_description'])) {
            throw new \RuntimeException('Please provide a valid `sonata_field_description` option');
        }

        return $options['sonata_field_description'];
    }

    /**
     * @return AdminInterface
     */
    protected function getAdmin(array $options)
    {
        return $this->getFieldDescription($options)->getAssociationAdmin();
    }
}
