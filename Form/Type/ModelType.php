<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;

class ModelType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        if ($options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeCollectionListener($options['model_manager']))
                ->prependClientTransformer(new ModelsToArrayTransformer($options['choice_list']));
        } else {
            $builder->prependClientTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'template'          => 'choice',
            'multiple'          => false,
            'expanded'          => false,
            'model_manager'     => null,
            'class'             => null,
            'property'          => null,
            'query'             => null,
            'choices'           => null,
            'parent'            => 'choice',
            'preferred_choices' => array(),
        );

        $options = array_replace($defaultOptions, $options);

        if (!isset($options['choice_list'])) {
            $defaultOptions['choice_list'] = new ModelChoiceList(
                $options['model_manager'],
                $options['class'],
                $options['property'],
                $options['query'],
                $options['choices']
            );
        }

        return $defaultOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(array $options)
    {
        return $options['parent'];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_model';
    }
}