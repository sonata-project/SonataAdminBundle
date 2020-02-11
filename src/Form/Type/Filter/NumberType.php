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

use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType as FormNumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class NumberType extends AbstractType
{
    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use NumberOperatorType::TYPE_GREATER_EQUAL instead
     */
    public const TYPE_GREATER_EQUAL = 1;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use NumberOperatorType::TYPE_GREATER_THAN instead
     */
    public const TYPE_GREATER_THAN = 2;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use NumberOperatorType::TYPE_EQUAL instead
     */
    public const TYPE_EQUAL = 3;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use NumberOperatorType::TYPE_LESS_EQUAL instead
     */
    public const TYPE_LESS_EQUAL = 4;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use NumberOperatorType::TYPE_LESS_THAN instead
     */
    public const TYPE_LESS_THAN = 5;

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
        return 'sonata_type_filter_number';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', NumberOperatorType::class, ['required' => false])
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
            'field_type' => FormNumberType::class,
            'field_options' => [],
        ]);
    }
}
