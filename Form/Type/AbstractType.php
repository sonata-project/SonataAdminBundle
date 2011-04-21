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

use Symfony\Component\Form\Type\AbstractType as BaseAbstractType;
use Symfony\Component\Form\Type\FormTypeInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\EventListener\ResizeFormListener;

abstract class AbstractType extends BaseAbstractType
{
    public function getDefaultOptions(array $options)
    {

        if (!isset($options['object'])) {
            throw new \RuntimeException('No `object` defined');
        }

        if (!isset($options['field_description'])) {
            throw new \RuntimeException('No `field_description` defined');
        }

        return array(
            'object'            => false,
            'field_description' => false,
            'entity_options'    => array()
        );
    }

    /**
     * @param array $options
     * @return Object
     */
    public function getObject(array $options)
    {
        return $options['object'];
    }

    /**
     * @throws \RuntimeException
     * @param array $options
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     */
    public function getFieldDescription(array $options)
    {
        return $options['field_description'];
    }
}