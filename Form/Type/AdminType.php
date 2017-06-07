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

use Doctrine\Common\Collections\Collection;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $admin = clone $this->getAdmin($options);

        if ($admin->hasParentFieldDescription()) {
            $admin->getParentFieldDescription()->setAssociationAdmin($admin);
        }

        if ($options['delete'] && $admin->hasAccess('delete')) {
            if (!array_key_exists('translation_domain', $options['delete_options']['type_options'])) {
                $options['delete_options']['type_options']['translation_domain'] = $admin->getTranslationDomain();
            }

            $builder->add(
                '_delete',
                $options['delete_options']['type'],
                array_merge(
                    $options['delete_options']['type_options'],
                    array(
                        'attr' => array('class' => 'sonata-admin-type-delete-checkbox'),
                    )
                )
            );
        }

        // hack to make sure the subject is correctly set
        // https://github.com/sonata-project/SonataAdminBundle/pull/2076
        if ($builder->getData() === null) {
            $p = new PropertyAccessor(false, true);
            try {
                $parentSubject = $admin->getParentFieldDescription()->getAdmin()->getSubject();
                if ($parentSubject !== null && $parentSubject !== false) {
                    // for PropertyAccessor < 2.5
                    // NEXT_MAJOR: remove this code for old PropertyAccessor after dropping support for Symfony 2.3
                    if (!method_exists($p, 'isReadable')) {
                        $subjectCollection = $p->getValue(
                            $parentSubject,
                            $this->getFieldDescription($options)->getFieldName()
                        );
                        if ($subjectCollection instanceof Collection) {
                            $subject = $subjectCollection->get(trim($options['property_path'], '[]'));
                        } else {
                            $subject = $subjectCollection;
                        }
                    } else {
                        // for PropertyAccessor >= 2.5
                        $subject = $p->getValue(
                            $parentSubject,
                            $this->getFieldDescription($options)->getFieldName().$options['property_path']
                        );
                    }
                    $builder->setData($subject);
                }
            } catch (NoSuchIndexException $e) {
                // no object here
            }
        }

        $admin->setSubject($builder->getData());

        $admin->defineFormBuilder($builder);

        $builder->addModelTransformer(new ArrayToModelTransformer($admin->getModelManager(), $admin->getClass()));
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'delete' => function (Options $options) {
                return $options['btn_delete'] !== false;
            },
            'delete_options' => array(
                'type' => 'checkbox',
                'type_options' => array(
                    'required' => false,
                    'mapped' => false,
                ),
            ),
            'auto_initialize' => false,
            'btn_add' => 'link_add',
            'btn_list' => 'link_list',
            'btn_delete' => 'link_delete',
            'btn_catalogue' => 'SonataAdminBundle',
        ));
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_type_admin';
    }

    /**
     * @param array $options
     *
     * @return FieldDescriptionInterface
     *
     * @throws \RuntimeException
     */
    protected function getFieldDescription(array $options)
    {
        if (!isset($options['sonata_field_description'])) {
            throw new \RuntimeException('Please provide a valid `sonata_field_description` option');
        }

        return $options['sonata_field_description'];
    }

    /**
     * @param array $options
     *
     * @return AdminInterface
     */
    protected function getAdmin(array $options)
    {
        return $this->getFieldDescription($options)->getAssociationAdmin();
    }
}
