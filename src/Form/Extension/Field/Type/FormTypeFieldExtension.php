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

namespace Sonata\AdminBundle\Form\Extension\Field\Type;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\NoValueException;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @psalm-suppress MissingTemplateParam https://github.com/phpstan/phpstan-symfony/issues/320
 */
final class FormTypeFieldExtension extends AbstractTypeExtension
{
    /**
     * @param array<string, string> $defaultClasses
     * @param array<string, mixed>  $options
     */
    public function __construct(
        private array $defaultClasses,
        private array $options
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sonataAdmin = [
            'name' => false,
            'admin' => false,
            'value' => null,
            'edit' => 'standard',
            'inline' => 'natural',
            'field_description' => null,
            'block_name' => false,
            'options' => $this->options,
        ];

        $builder->setAttribute('sonata_admin_enabled', false);

        if ($options['sonata_field_description'] instanceof FieldDescriptionInterface) {
            $fieldDescription = $options['sonata_field_description'];

            $sonataAdmin['admin'] = $fieldDescription->getAdmin();
            $sonataAdmin['field_description'] = $fieldDescription;
            $sonataAdmin['name'] = $fieldDescription->getName();
            $sonataAdmin['edit'] = $fieldDescription->getOption('edit', 'standard');
            $sonataAdmin['inline'] = $fieldDescription->getOption('inline', 'natural');
            $sonataAdmin['block_name'] = $fieldDescription->getOption('block_name', false);
            $sonataAdmin['class'] = $this->getClass($builder);

            $builder->setAttribute('sonata_admin_enabled', true);
        }

        $builder->setAttribute('sonata_admin', $sonataAdmin);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $sonataAdmin = $form->getConfig()->getAttribute('sonata_admin');

        /*
         * We have a child, so we need to upgrade block prefix
         */
        if (
            null !== $view->parent
            && true === $view->parent->vars['sonata_admin_enabled']
            && \is_array($sonataAdmin)
            && false === $sonataAdmin['admin']
        ) {
            $blockPrefixes = $view->vars['block_prefixes'] ?? [];
            \assert(\is_array($blockPrefixes));

            $adminCode = $view->parent->vars['sonata_admin_code'] ?? '';
            \assert(\is_string($adminCode));

            $baseName = str_replace('.', '_', $adminCode);

            $baseType = $blockPrefixes[\count($blockPrefixes) - 2];
            $lastBlockPrefix = end($blockPrefixes);
            \assert(\is_string($lastBlockPrefix));

            $blockSuffix = preg_replace('#^_([a-z0-9]{14})_(.++)$#', '$2', $lastBlockPrefix);

            $blockPrefixes[] = sprintf('%s_%s', $baseName, $baseType);
            $blockPrefixes[] = sprintf('%s_%s_%s_%s', $baseName, $baseType, $view->parent->vars['name'], $view->vars['name']);
            $blockPrefixes[] = sprintf('%s_%s_%s_%s', $baseName, $baseType, $view->parent->vars['name'], $blockSuffix);

            $view->vars['block_prefixes'] = array_unique($blockPrefixes);
            $view->vars['sonata_admin_enabled'] = true;
            $view->vars['sonata_admin'] = [
                'admin' => false,
                'field_description' => false,
                'name' => false,
                'edit' => 'standard',
                'inline' => 'natural',
                'block_name' => false,
                'class' => false,
                'options' => $this->options,
            ];
            $view->vars['sonata_admin_code'] = $adminCode;

            return;
        }

        // avoid to add extra information not required by non admin field
        if (\is_array($sonataAdmin) && true === $form->getConfig()->getAttribute('sonata_admin_enabled', true)) {
            $sonataAdmin['value'] = $form->getData();

            // add a new block types, so the Admin Form element can be tweaked based on the admin code
            $blockPrefixes = $view->vars['block_prefixes'] ?? [];
            \assert(\is_array($blockPrefixes));

            $admin = $sonataAdmin['admin'];
            \assert($admin instanceof AdminInterface);

            $baseName = str_replace('.', '_', $admin->getCode());
            $baseType = $blockPrefixes[\count($blockPrefixes) - 2];
            $lastBlockPrefix = end($blockPrefixes);
            \assert(\is_string($lastBlockPrefix));

            $blockSuffix = preg_replace('#^_([a-z0-9]{14})_(.++)$#', '$2', $lastBlockPrefix);

            $blockPrefixes[] = sprintf('%s_%s', $baseName, $baseType);
            $blockPrefixes[] = sprintf('%s_%s_%s', $baseName, $sonataAdmin['name'], $baseType);
            $blockPrefixes[] = sprintf('%s_%s_%s_%s', $baseName, $sonataAdmin['name'], $baseType, $blockSuffix);

            if (isset($sonataAdmin['block_name']) && false !== $sonataAdmin['block_name']) {
                $blockPrefixes[] = $sonataAdmin['block_name'];
            }

            $view->vars['block_prefixes'] = array_unique($blockPrefixes);
            $view->vars['sonata_admin_enabled'] = true;
            $view->vars['sonata_admin'] = $sonataAdmin;
            $view->vars['sonata_admin_code'] = $admin->getCode();
            $view->vars['sonata_admin_translation_domain'] = $admin->getTranslationDomain();

            $attr = $view->vars['attr'];

            if (!isset($attr['class']) && isset($sonataAdmin['class'])) {
                $attr['class'] = $sonataAdmin['class'];
            }

            $view->vars['attr'] = $attr;
        } else {
            $view->vars['sonata_admin_enabled'] = false;
        }

        $view->vars['sonata_admin'] = $sonataAdmin;
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
            'sonata_admin' => null,
            'sonata_field_description' => null,

            // be compatible with mopa if not installed, avoid generating an exception for invalid option
            'label_render' => true,
        ]);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/admin-bundle version 4.3 and will be removed in 5.0.
     *
     * return the value related to FieldDescription, if the associated object does no
     * exists => a temporary one is created.
     */
    public function getValueFromFieldDescription(?object $object, FieldDescriptionInterface $fieldDescription): mixed
    {
        @trigger_error(sprintf(
            'The method "%s()" is deprecated since sonata-project/admin-bundle 4.3 and will be removed in 5.0.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $value = null;

        if (null === $object) {
            return null;
        }

        try {
            $value = $fieldDescription->getValue($object);
        } catch (NoValueException) {
            if ($fieldDescription->hasAssociationAdmin()) {
                $value = $fieldDescription->getAssociationAdmin()->getNewInstance();
            }
        }

        return $value;
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
