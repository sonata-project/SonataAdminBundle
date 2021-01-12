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

namespace Sonata\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Amine Zaghdoudi <amine.zaghdoudi@ekino.com>
 */
class ChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $optionalOptions = ['sortable'];

        $resolver->setDefined($optionalOptions);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['sortable'] = \array_key_exists('sortable', $options) && $options['sortable'];
    }

    /**
     * @return string
     *
     * @phpstan-return class-string<FormTypeInterface>
     */
    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    /**
     * @return string[]
     *
     * @phpstan-return class-string<FormTypeInterface>[]
     */
    public static function getExtendedTypes()
    {
        return [ChoiceType::class];
    }
}
