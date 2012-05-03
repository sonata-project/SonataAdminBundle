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
use Symfony\Component\Form\FormBuilder;

use Sonata\AdminBundle\Form\EventListener\ResizeFormListener;

class ImmutableArrayType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        foreach ($options['keys'] as $infos) {
            if ($infos instanceof FormBuilder) {
                $builder->add($infos);
            } else {
                list($name, $type, $options) = $infos;
                $builder->add($name, $type, $options);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'keys'    => array(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_immutable_array';
    }
}