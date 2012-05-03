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

class ModelReferenceType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->prependClientTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']));
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'model_manager'     => null,
            'class'             => null,
            'parent'            => 'hidden',
        );

        $options = array_replace($defaultOptions, $options);

        return $options;
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
        return 'sonata_type_model_reference';
    }
}