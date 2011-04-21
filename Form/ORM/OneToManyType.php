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

namespace Sonata\AdminBundle\Form\ORM;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\EventListener\MergeCollectionListener;
use Symfony\Bridge\Doctrine\Form\DataTransformer\EntitiesToArrayTransformer;
use Symfony\Bridge\Doctrine\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Form\Type\AbstractType;

class OneToManyType extends AbstractType
{

    public function getName()
    {
        return 'sonata_admin_doctrine_orm';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if ($options['multiple']) {
            $builder->addEventSubscriber(new MergeCollectionListener())
                ->prependClientTransformer(new EntitiesToArrayTransformer($options['choice_list']));
        } else {
            $builder->prependClientTransformer(new EntityToIdTransformer($options['choice_list']));
        }
    }

    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'template'  => 'choice',
            'multiple'  => false,
            'expanded'  => false,
            'em'        => null,
            'class'     => null,
            'property'  => null,
            'query_builder' => null,
            'choices'   => array(),
            'preferred_choices' => array(),
            'multiple'  => false,
            'expanded'  => false,

            // admin specific code
            'field_description' => false,
            'edit'              => 'standard'
        );

        $options = array_replace($defaultOptions, $options);

        if (!isset($options['choice_list'])) {
            $defaultOptions['choice_list'] = new EntityChoiceList(
                $options['em'],
                $options['class'],
                $options['property'],
                $options['query_builder'],
                $options['choices']
            );
        }

        return $defaultOptions;
    }

    public function getParent(array $options)
    {
        if($options['edit'] == 'list') {
            return 'text';
        }

        return 'choice';
    }
}