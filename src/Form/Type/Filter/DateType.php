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

use Sonata\AdminBundle\Form\Type\Operator\DateOperatorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType as FormDateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DateType extends AbstractType
{
    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateOperatorType::TYPE_GREATER_EQUAL instead
     */
    public const TYPE_GREATER_EQUAL = 1;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateOperatorType::TYPE_GREATER_THAN instead
     */
    public const TYPE_GREATER_THAN = 2;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateOperatorType::TYPE_EQUAL instead
     */
    public const TYPE_EQUAL = 3;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateOperatorType::TYPE_LESS_EQUAL instead
     */
    public const TYPE_LESS_EQUAL = 4;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateOperatorType::TYPE_LESS_THAN instead
     */
    public const TYPE_LESS_THAN = 5;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateOperatorType::TYPE_NULL instead
     */
    public const TYPE_NULL = 6;

    /**
     * @deprecated since sonata-project/admin-bundle 3.57, to be removed with 4.0: Use DateOperatorType::TYPE_NOT_NULL instead
     */
    public const TYPE_NOT_NULL = 7;

    /**
     * NEXT_MAJOR: remove this property.
     *
     * @deprecated since sonata-project/admin-bundle 3.5, to be removed with 4.0
     *
     * @var TranslatorInterface|LegacyTranslatorInterface
     */
    protected $translator;

    public function __construct($translator)
    {
        if (!$translator instanceof LegacyTranslatorInterface && !$translator instanceof TranslatorInterface) {
            throw new \TypeError(sprintf(
                'Argument 1 passed to "%s()" must be an instance of "%s" or "%s", %s given.',
                __METHOD__,
                LegacyTranslatorInterface::class,
                TranslatorInterface::class,
                \is_object($translator) ? 'instance of '.\get_class($translator) : \gettype($translator)
            ));
        }

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
        return 'sonata_type_filter_date';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', DateOperatorType::class, ['required' => false])
            ->add('value', $options['field_type'], array_merge(['required' => false], $options['field_options']))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_type' => FormDateType::class,
            'field_options' => ['date_format' => 'yyyy-MM-dd'],
        ]);
    }
}
