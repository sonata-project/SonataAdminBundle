<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Form\Extension\Field\Type;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class FormTypeFieldExtension extends AbstractTypeExtension
{
    /**
     * @param array<string, string> $defaultClasses
     * @param array<string, mixed>  $options
     */
    public function __construct(
        private array $defaultClasses,
        private array $options,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sensiolabsAdmin = [
            'name' => false,
            'admin' => false,
            'value' => null,
            'edit' => 'standard',
            'inline' => 'natural',
            'field_description' => null,
            'block_name' => false,
            'options' => $this->options,
        ];

        $builder->setAttribute('sensiolabs_admin_enabled', false);

        if ($options['sensiolabs_field_description'] instanceof FieldDescriptionInterface) {
            $fieldDescription = $options['sensiolabs_field_description'];

            $sensiolabsAdmin['admin'] = $fieldDescription->getAdmin();
            $sensiolabsAdmin['field_description'] = $fieldDescription;
            $sensiolabsAdmin['name'] = $fieldDescription->getName();
            $sensiolabsAdmin['edit'] = $fieldDescription->getOption('edit', 'standard');
            $sensiolabsAdmin['inline'] = $fieldDescription->getOption('inline', 'natural');
            $sensiolabsAdmin['block_name'] = $fieldDescription->getOption('block_name', false);
            $sensiolabsAdmin['class'] = $this->getClass($builder);

            $builder->setAttribute('sensiolabs_admin_enabled', true);
        }

        $builder->setAttribute('sensiolabs_admin', $sensiolabsAdmin);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $sensiolabsAdmin = $form->getConfig()->getAttribute('sensiolabs_admin');

        /*
         * We have a child, so we need to upgrade block prefix
         */
        if (
            null !== $view->parent
            && true === $view->parent->vars['sensiolabs_admin_enabled']
            && \is_array($sensiolabsAdmin)
            && false === $sensiolabsAdmin['admin']
        ) {
            $blockPrefixes = $view->vars['block_prefixes'] ?? [];
            \assert(\is_array($blockPrefixes));

            $adminCode = $view->parent->vars['sensiolabs_admin_code'] ?? '';
            \assert(\is_string($adminCode));

            $baseName = str_replace('.', '_', $adminCode);

            $baseType = $blockPrefixes[\count($blockPrefixes) - 2];
            $lastBlockPrefix = end($blockPrefixes);
            \assert(\is_string($lastBlockPrefix));

            $blockSuffix = preg_replace('#^_([a-z0-9]{14})_(.++)$#', '$2', $lastBlockPrefix);
            \assert(\is_string($blockSuffix));

            $blockPrefixes[] = \sprintf('%s_%s', $baseName, $baseType);
            $blockPrefixes[] = \sprintf('%s_%s_%s_%s', $baseName, $baseType, $view->parent->vars['name'], $view->vars['name']);
            $blockPrefixes[] = \sprintf('%s_%s_%s_%s', $baseName, $baseType, $view->parent->vars['name'], $blockSuffix);

            $view->vars['block_prefixes'] = array_unique($blockPrefixes);
            $view->vars['sensiolabs_admin_enabled'] = true;
            $view->vars['sensiolabs_admin'] = [
                'admin' => false,
                'field_description' => false,
                'name' => false,
                'edit' => 'standard',
                'inline' => 'natural',
                'block_name' => false,
                'class' => false,
                'options' => $this->options,
            ];
            $view->vars['sensiolabs_admin_code'] = $adminCode;

            return;
        }

        // avoid to add extra information not required by non admin field
        if (\is_array($sensiolabsAdmin) && true === $form->getConfig()->getAttribute('sensiolabs_admin_enabled', true)) {
            $sensiolabsAdmin['value'] = $form->getData();

            // add a new block types, so the Admin Form element can be tweaked based on the admin code
            $blockPrefixes = $view->vars['block_prefixes'] ?? [];
            \assert(\is_array($blockPrefixes));

            $admin = $sensiolabsAdmin['admin'];
            \assert($admin instanceof AdminInterface);

            $baseName = str_replace('.', '_', $admin->getCode());
            $baseType = $blockPrefixes[\count($blockPrefixes) - 2];
            $lastBlockPrefix = end($blockPrefixes);
            \assert(\is_string($lastBlockPrefix));

            $blockSuffix = preg_replace('#^_([a-z0-9]{14})_(.++)$#', '$2', $lastBlockPrefix);
            \assert(\is_string($blockSuffix));

            $blockPrefixes[] = \sprintf('%s_%s', $baseName, $baseType);
            $blockPrefixes[] = \sprintf('%s_%s_%s', $baseName, $sensiolabsAdmin['name'], $baseType);
            $blockPrefixes[] = \sprintf('%s_%s_%s_%s', $baseName, $sensiolabsAdmin['name'], $baseType, $blockSuffix);

            if (isset($sensiolabsAdmin['block_name']) && false !== $sensiolabsAdmin['block_name']) {
                $blockPrefixes[] = $sensiolabsAdmin['block_name'];
            }

            $view->vars['block_prefixes'] = array_unique($blockPrefixes);
            $view->vars['sensiolabs_admin_enabled'] = true;
            $view->vars['sensiolabs_admin'] = $sensiolabsAdmin;
            $view->vars['sensiolabs_admin_code'] = $admin->getCode();
            $view->vars['sensiolabs_admin_translation_domain'] = $admin->getTranslationDomain();

            $attr = $view->vars['attr'];

            if (!isset($attr['class']) && isset($sensiolabsAdmin['class'])) {
                $attr['class'] = $sensiolabsAdmin['class'];
            }

            $view->vars['attr'] = $attr;
        } else {
            $view->vars['sensiolabs_admin_enabled'] = false;
        }

        $view->vars['sensiolabs_admin'] = $sensiolabsAdmin;
    }

    /**
     * @return string[]
     *
     * @phpstan-return class-string<FormTypeInterface>[]
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'sensiolabs_admin' => null,
            'sensiolabs_field_description' => null,

            // be compatible with mopa if not installed, avoid generating an exception for invalid option
            'label_render' => true,
        ]);
    }

    private function getClass(FormBuilderInterface $formBuilder): string
    {
        foreach ($this->getTypes($formBuilder) as $type) {
            $name = $type::class;

            if (isset($this->defaultClasses[$name])) {
                return $this->defaultClasses[$name];
            }
        }

        return '';
    }

    /**
     * @return FormTypeInterface[]
     */
    private function getTypes(FormBuilderInterface $formBuilder): array
    {
        $types = [];

        for ($type = $formBuilder->getType(); null !== $type; $type = $type->getParent()) {
            array_unshift($types, $type->getInnerType());
        }

        return $types;
    }
}
