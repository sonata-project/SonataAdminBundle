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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Sonata\AdminBundle\Form\DataTransformer\BooleanToSonataBooleanTransformer;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BooleanType extends AbstractType
{
    const TYPE_YES = 1;

    const TYPE_NO = 2;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new BooleanToSonataBooleanTransformer());
    }
    
    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'catalogue' => 'SonataAdminBundle',
            'choices'   => array(
                self::TYPE_YES  => 'label_type_yes',
                self::TYPE_NO   => 'label_type_no'
            )
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'sonata_type_translatable_choice';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_boolean';
    }
}
