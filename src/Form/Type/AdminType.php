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

use Sonata\AdminBundle\Admin\AdminHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Sonata\AdminBundle\Manipulator\ObjectManipulator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AdminType extends AbstractType
{
    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var AdminHelper|null
     */
    private $adminHelper;

    /**
     * NEXT_MAJOR: Remove the __construct method.
     */
    public function __construct(?AdminHelper $adminHelper = null)
    {
        if (null !== $adminHelper) {
            @trigger_error(sprintf(
                'Passing argument 1 to %s() is deprecated since sonata-project/admin-bundle 3.72'
                .' and will be ignored in version 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        $this->adminHelper = $adminHelper;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $admin = clone $this->getAdmin($options);

        if ($admin->hasParentFieldDescription()) {
            $admin->getParentFieldDescription()->setAssociationAdmin($admin);
        }

        if ($options['delete'] && $admin->hasAccess('delete')) {
            if (!\array_key_exists('translation_domain', $options['delete_options']['type_options'])) {
                $options['delete_options']['type_options']['translation_domain'] = $admin->getTranslationDomain();
            }

            $builder->add('_delete', $options['delete_options']['type'], $options['delete_options']['type_options']);
        }

        // hack to make sure the subject is correctly set
        // https://github.com/sonata-project/SonataAdminBundle/pull/2076
        if (null === $builder->getData()) {
            $p = new PropertyAccessor(false, true);

            if ($admin->hasParentFieldDescription()) {
                $parentFieldDescription = $admin->getParentFieldDescription();
                $parentAdmin = $parentFieldDescription->getAdmin();

                if ($parentAdmin->hasSubject() && isset($options['property_path'])) {
                    // this check is to work around duplication issue in property path
                    // https://github.com/sonata-project/SonataAdminBundle/issues/4425
                    if ($this->getFieldDescription($options)->getFieldName() === $options['property_path']) {
                        $path = $options['property_path'];
                    } else {
                        $path = $this->getFieldDescription($options)->getFieldName().$options['property_path'];
                    }

                    $parentPath = implode(
                        '',
                        array_map(
                            static function (array $associationMapping): string {
                                return sprintf('%s.', $associationMapping['fieldName']);
                            },
                            $this->getFieldDescription($options)->getParentAssociationMappings()
                        )
                    );
                    $parentSubject = $parentAdmin->getSubject();

                    try {
                        $subject = $p->getValue($parentSubject, $parentPath.$path);
                    } catch (NoSuchIndexException $e) {
                        // no object here, we create a new one
                        $subject = $admin->getNewInstance();

                        if ($options['collection_by_reference']) {
                            $subject = ObjectManipulator::addInstance($parentSubject, $subject, $parentFieldDescription);
                        } else {
                            $subject = ObjectManipulator::setObject($subject, $parentSubject, $parentFieldDescription);
                        }
                    }
                }
            }

            $builder->setData($subject ?? $admin->getNewInstance());
        }

        $admin->setSubject($builder->getData());

        $admin->defineFormBuilder($builder);

        $builder->addModelTransformer(new ArrayToModelTransformer($admin->getModelManager(), $admin->getClass()));
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['btn_add'] = $options['btn_add'];
        $view->vars['btn_list'] = $options['btn_list'];
        $view->vars['btn_delete'] = $options['btn_delete'];
        $view->vars['btn_catalogue'] = $options['btn_catalogue'];
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'delete' => static function (Options $options) {
                return false !== $options['btn_delete'];
            },
            'delete_options' => [
                'type' => CheckboxType::class,
                'type_options' => [
                    'required' => false,
                    'mapped' => false,
                ],
            ],
            'auto_initialize' => false,
            'btn_add' => 'link_add',
            'btn_list' => 'link_list',
            'btn_delete' => 'link_delete',
            'btn_catalogue' => 'SonataAdminBundle',
            'collection_by_reference' => true,
        ]);
    }

    /**
     * NEXT_MAJOR: Remove when dropping Symfony <2.8 support.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'sonata_type_admin';
    }

    /**
     * @param array<string, mixed> $options
     *
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
     * @param array<string, mixed> $options
     *
     * @return AdminInterface
     */
    protected function getAdmin(array $options)
    {
        return $this->getFieldDescription($options)->getAssociationAdmin();
    }
}
