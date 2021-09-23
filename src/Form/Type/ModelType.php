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

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceLoader;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
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
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
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
        $view->vars['btn_catalogue'] = $options['btn_catalogue'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $options = [];

        $options['choice_loader'] = function (Options $options, ?ChoiceListInterface $previousValue) {
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
            'compound' => static function (Options $options): bool {
                if (isset($options['multiple']) && $options['multiple']) {
                    if (isset($options['expanded']) && $options['expanded']) {
                        //checkboxes
                        return true;
                    }

                    //select tag (with multiple attribute)
                    return false;
                }

                if (isset($options['expanded']) && $options['expanded']) {
                    //radio buttons
                    return true;
                }

                //select tag
                return false;
            },

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
            'btn_catalogue' => 'SonataAdminBundle',
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
        return 'sonata_type_model';
    }
}
