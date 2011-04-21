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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;

class ManyToOneType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {

        die(1);
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        die(2);
    }

    public function buildViewBottomUp(FormView $view, FormInterface $form)
    {
        die(3);
    }

    public function createBuilder($name, FormFactoryInterface $factory, array $options)
    {
        die(4);
        return null;
    }

    public function getName()
    {
        return 'doctrine_orm_many_to_one';
    }


    public function getParent(array $options)
    {
        return 'test';
    }
}