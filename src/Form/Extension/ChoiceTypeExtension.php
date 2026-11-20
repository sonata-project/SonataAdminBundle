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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Amine Zaghdoudi <amine.zaghdoudi@ekino.com>
 */
final class ChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true !== ($options['sortable'] ?? false) || true !== $options['multiple'] || true === $options['expanded']) {
            return;
        }

        // The sortable widget stores the selected values as a comma separated list in a single hidden input.
        // On a regular submit the javascript splits it into separate inputs, but not when the form is submitted via ajax
        // (e.g. to retrieve or append a form element). The priority must be higher than the one of the ChoiceType listener,
        // wich would otherwise discard the comma separated value as an unknown choice.
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();

            if (!\is_array($data) || 1 !== \count($data)) {
                return;
            }

            $value = reset($data);

            if (!\is_string($value) || !str_contains($value, ',')) {
                return;
            }

            $event->setData(explode(',', $value));
        }, 1);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $optionalOptions = ['sortable'];

        $resolver->setDefined($optionalOptions);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['sortable'] = true === ($options['sortable'] ?? false);
    }

    /**
     * @return string[]
     *
     * @phpstan-return class-string<FormTypeInterface>[]
     */
    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }
}
