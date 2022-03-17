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

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TemplateType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $parameters = [];

        $fieldDescription = $view->vars['sonata_admin']['field_description'] ?? null;
        if ($fieldDescription instanceof FieldDescriptionInterface) {
            $parameters['field_description'] = $fieldDescription;
            $parameters['admin'] = $fieldDescription->getAdmin();
            $parameters['object'] = $fieldDescription->getAdmin()->getSubject();
        }

        $view->vars['template'] = $options['template'];
        $view->vars['parameters'] = array_merge($options['parameters'], $parameters);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('template');
        $resolver->setAllowedTypes('template', 'string');

        $resolver->setDefaults([
            'parameters' => [],
            'mapped' => false,
            'required' => false,
        ]);
        $resolver->setAllowedTypes('parameters', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_type_template';
    }
}
