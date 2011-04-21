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

use Sonata\AdminBundle\Form\Type\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\EventListener\ResizeFormListener;

class ManyToManyType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {

    }

    public function getName()
    {
        return 'doctrine_orm_many_to_many';
    }
}