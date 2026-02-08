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

namespace SensioLabs\AdminBundle\Form\Type;

use SensioLabs\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use SensioLabs\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use SensioLabs\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use SensioLabs\AdminBundle\Form\EventListener\MergeCollectionListener;
use SensioLabs\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * This type define a standard select input with a + sign to add new associated object.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ModelType extends AbstractType
{
    public function __construct(
        private PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true === $options['multiple']) {
            $builder->addViewTransformer(
                new ModelsToArrayTransformer($options['model_manager'], $options['class']),
                true
            );

            $builder->addEventSubscriber(new MergeCollectionListener());
        } else {
            $builder
                ->addViewTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']), true);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['btn_add'] = $options['btn_add'];
        $view->vars['btn_list'] = $options['btn_list'];
        $view->vars['btn_delete'] = $options['btn_delete'];

        $view->vars['btn_translation_domain'] = $options['btn_translation_domain'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $options = [];

        $options['choice_loader'] = function (Options $options, ?ChoiceListInterface $previousValue): array|ChoiceLoaderInterface {
            if (null !== $previousValue && \count($choices = $previousValue->getChoices()) > 0) {
                return $choices;
            }

            return new ModelChoiceLoader(
                $options['model_manager'],
                $this->propertyAccessor,
                $options['class'],
                $options['property'],
                $options['query'],
                $options['choices'],
            );
        };

        $resolver->setDefaults(array_merge($options, [
            'compound' => static fn (Options $options): bool => true === $options['expanded'],
            'template' => 'choice',
            'multiple' => false,
            'expanded' => false,
            'property' => null,
            'query' => null,
            'choices' => null,
            'preferred_choices' => [],
            'btn_add' => 'link_add',
            'btn_list' => 'link_list',
            'btn_delete' => 'link_delete',
            'btn_translation_domain' => 'SensioLabsAdminBundle',
        ]));

        $resolver->setRequired(['model_manager', 'class']);
        $resolver->setAllowedTypes('model_manager', ModelManagerInterface::class);
        $resolver->setAllowedTypes('class', 'string');

    }

    /**
     * @phpstan-return class-string<FormTypeInterface>
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sensiolabs_type_model';
    }
}
