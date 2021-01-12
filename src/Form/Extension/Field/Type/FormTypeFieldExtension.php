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

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Exception\NoValueException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @final since sonata-project/admin-bundle 3.52
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class FormTypeFieldExtension extends AbstractTypeExtension
{
    /**
     * @var array<string, string>
     */
    protected $defaultClasses = [];

    /**
     * @var array<string, mixed>
     */
    protected $options = [];

    /**
     * FormTypeFieldExtension constructor.
     *
     * @param array<string, string> $defaultClasses
     * @param array<string, mixed>  $options
     */
    public function __construct(array $defaultClasses, array $options)
    {
        $this->defaultClasses = $defaultClasses;
        $this->options = $options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sonataAdmin = [
            'name' => null,
            'admin' => null,
            'value' => null,
            'edit' => 'standard',
            'inline' => 'natural',
            'field_description' => null,
            'block_name' => false,
            'options' => $this->options,
        ];

        $builder->setAttribute('sonata_admin_enabled', false);
        // NEXT_MAJOR: Remove this line
        $builder->setAttribute('sonata_help', false);

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
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $sonataAdmin = $form->getConfig()->getAttribute('sonata_admin');
        // NEXT_MAJOR: Remove this line
        $sonataAdminHelp = $options['sonata_help'] ?? null;

        /*
         * We have a child, so we need to upgrade block prefix
         */
        if ($view->parent && $view->parent->vars['sonata_admin_enabled'] && !$sonataAdmin['admin']) {
            $blockPrefixes = $view->vars['block_prefixes'];
            $baseName = str_replace('.', '_', $view->parent->vars['sonata_admin_code']);

            $baseType = $blockPrefixes[\count($blockPrefixes) - 2];
            $blockSuffix = preg_replace('#^_([a-z0-9]{14})_(.++)$#', '$2', end($blockPrefixes));

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
            // NEXT_MAJOR: Remove this line
            $view->vars['sonata_help'] = $sonataAdminHelp;
            $view->vars['sonata_admin_code'] = $view->parent->vars['sonata_admin_code'];

            return;
        }

        // avoid to add extra information not required by non admin field
        if ($sonataAdmin && $form->getConfig()->getAttribute('sonata_admin_enabled', true)) {
            $sonataAdmin['value'] = $form->getData();

            // add a new block types, so the Admin Form element can be tweaked based on the admin code
            $blockPrefixes = $view->vars['block_prefixes'];
            $baseName = str_replace('.', '_', $sonataAdmin['admin']->getCode());
            $baseType = $blockPrefixes[\count($blockPrefixes) - 2];
            $blockSuffix = preg_replace('#^_([a-z0-9]{14})_(.++)$#', '$2', end($blockPrefixes));

            $blockPrefixes[] = sprintf('%s_%s', $baseName, $baseType);
            $blockPrefixes[] = sprintf('%s_%s_%s', $baseName, $sonataAdmin['name'], $baseType);
            $blockPrefixes[] = sprintf('%s_%s_%s_%s', $baseName, $sonataAdmin['name'], $baseType, $blockSuffix);

            if (isset($sonataAdmin['block_name']) && false !== $sonataAdmin['block_name']) {
                $blockPrefixes[] = $sonataAdmin['block_name'];
            }

            $view->vars['block_prefixes'] = array_unique($blockPrefixes);
            $view->vars['sonata_admin_enabled'] = true;
            $view->vars['sonata_admin'] = $sonataAdmin;
            $view->vars['sonata_admin_code'] = $sonataAdmin['admin']->getCode();

            $attr = $view->vars['attr'];

            if (!isset($attr['class']) && isset($sonataAdmin['class'])) {
                $attr['class'] = $sonataAdmin['class'];
            }

            $view->vars['attr'] = $attr;
        } else {
            $view->vars['sonata_admin_enabled'] = false;
        }

        // NEXT_MAJOR: Remove this line
        $view->vars['sonata_help'] = $sonataAdminHelp;
        $view->vars['sonata_admin'] = $sonataAdmin;
    }

    /**
     * @return string
     *
     * @phpstan-return class-string<FormTypeInterface>
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * @return string[]
     *
     * @phpstan-return class-string<FormTypeInterface>[]
     */
    public static function getExtendedTypes()
    {
        return [FormType::class];
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
            'sonata_admin' => null,
            'sonata_field_description' => null,

            // be compatible with mopa if not installed, avoid generating an exception for invalid option
            'label_render' => true,
            // NEXT_MAJOR: Remove this property and the deprecation message
            'sonata_help' => null,
        ]);

        // BC layer for symfony/options-resolver < 5.1.
        // @todo: Remove the check and the contents of the `else` condition when dropping the support for lower versions.
        if (method_exists($resolver, 'getInfo')) {
            $resolver
                ->setDeprecated(
                    'sonata_help',
                    'sonata-project/admin-bundle',
                    '3.60',
                    'The %name% option is deprecated since sonata-project/admin-bundle 3.60, to be removed in 4.0. Use "help" instead.'
                );
        } else {
            $resolver
                ->setDeprecated(
                    'sonata_help',
                    'The "sonata_help" option is deprecated since sonata-project/admin-bundle 3.60, to be removed in 4.0. Use "help" instead.'
                );
        }
    }

    /**
     * return the value related to FieldDescription, if the associated object does no
     * exists => a temporary one is created.
     *
     * @param object|null $object
     *
     * @return mixed
     */
    public function getValueFromFieldDescription($object, FieldDescriptionInterface $fieldDescription)
    {
        $value = null;

        if (!$object) {
            return null;
        }

        try {
            $value = $fieldDescription->getValue($object);
        } catch (NoValueException $e) {
            if ($fieldDescription->hasAssociationAdmin()) {
                $value = $fieldDescription->getAssociationAdmin()->getNewInstance();
            }
        }

        return $value;
    }

    /**
     * @return string
     */
    protected function getClass(FormBuilderInterface $formBuilder)
    {
        foreach ($this->getTypes($formBuilder) as $type) {
            $name = \get_class($type);

            if (isset($this->defaultClasses[$name])) {
                return $this->defaultClasses[$name];
            }
        }

        return '';
    }

    /**
     * @return array
     */
    protected function getTypes(FormBuilderInterface $formBuilder)
    {
        $types = [];

        for ($type = $formBuilder->getType(); null !== $type; $type = $type->getParent()) {
            array_unshift($types, $type->getInnerType());
        }

        return $types;
    }
}
