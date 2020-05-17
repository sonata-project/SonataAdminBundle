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
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Amine Zaghdoudi <amine.zaghdoudi@ekino.com>
 */
class ChoiceTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $optionalOptions = ['sortable'];

        $resolver->setDefined($optionalOptions);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['sortable'] = \array_key_exists('sortable', $options) && $options['sortable'];
    }

    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }
}
