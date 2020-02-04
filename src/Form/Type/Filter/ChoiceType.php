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

namespace Sonata\AdminBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as FormChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ChoiceType extends AbstractType
{
    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use ContainsOperatorType::TYPE_CONTAINS instead
     */
    public const TYPE_CONTAINS = 1;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use ContainsOperatorType::TYPE_NOT_CONTAINS instead
     */
    public const TYPE_NOT_CONTAINS = 2;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use ContainsOperatorType::TYPE_EQUAL instead
     */
    public const TYPE_EQUAL = 3;

    /**
     * NEXT_MAJOR: remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.5, to be removed with 4.0
     *
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
        return 'sonata_type_filter_choice';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', $options['operator_type'], array_merge(['required' => false], $options['operator_options']))
            ->add('value', $options['field_type'], array_merge(['required' => false], $options['field_options']))
        ;
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
            'field_type' => FormChoiceType::class,
            'field_options' => [],
            'operator_type' => ContainsOperatorType::class,
            'operator_options' => [],
        ]);
    }
}
