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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ChoiceFieldMaskType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $sanitizedMap = [];
        foreach ($options['map'] as $value => $fieldNames) {
            foreach ($fieldNames as $fieldName) {
                $sanitizedMap[$value][] =
                    str_replace(['__', '.'], ['____', '__'], $fieldName);
            }
        }

        $allFieldNames = call_user_func_array('array_merge', $sanitizedMap);
        $allFieldNames = array_unique($allFieldNames);

        $view->vars['all_fields'] = $allFieldNames;
        $view->vars['map'] = $sanitizedMap;

        $options['expanded'] = false;

        parent::buildView($view, $form, $options);
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
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'map' => [],
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
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
        return 'sonata_type_choice_field_mask';
    }
}
