<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Extension;

use Sonata\AdminBundle\Form\Extension\ChoiceTypeExtension;
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Sonata\CoreBundle\Form\Extension\DependencyInjectionExtension;
use Symfony\Component\Form\Forms;

class ChoiceTypeExtensionTest extends PHPUnit_Framework_TestCase
{
    protected function setup()
    {
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
            $container->expects($this->any())->method('has')->will($this->returnValue(true));
            $container->expects($this->any())->method('get')
                ->with($this->equalTo('sonata.admin.form.choice_extension'))
                ->will($this->returnValue(new ChoiceTypeExtension()));

            $typeServiceIds = array();
            $typeExtensionServiceIds = array();
            $guesserServiceIds = array();
            $mappingTypes = array(
                'choice' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            );
            $extensionTypes = array(
                'choice' => array(
                    'sonata.admin.form.choice_extension',
                ),
            );

            $dependency = new DependencyInjectionExtension(
                $container,
                $typeServiceIds,
                $typeExtensionServiceIds,
                $guesserServiceIds,
                $mappingTypes,
                $extensionTypes
            );

            $this->factory = Forms::createFormFactoryBuilder()
                ->addExtension($dependency)
                ->getFormFactory();
        } else {
            $this->factory = Forms::createFormFactoryBuilder()
                  ->addTypeExtension(new ChoiceTypeExtension())
                  ->getFormFactory();
        }
    }

    public function testExtendedType()
    {
        $extension = new ChoiceTypeExtension();

        /*
         * NEXT_MAJOR: Remove when dropping Symfony <2.8 support. It should
         * simply be:
         *
         * $this->assertSame(
         *     'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
         *     $extension->getExtendedType()
         * );
         */
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $this->assertSame(
                'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
                $extension->getExtendedType()
            );
        } else {
            $this->assertSame('choice', $extension->getExtendedType());
        }
    }

    public function testDefaultOptionsWithSortable()
    {
        $name = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType' : 'choice';

        $view = $this->factory
            ->create($name, null, array(
                'sortable' => true,
            ))
            ->createView();

        $this->assertTrue(isset($view->vars['sortable']));
        $this->assertTrue($view->vars['sortable']);
    }

    public function testDefaultOptionsWithoutSortable()
    {
        $name = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType' : 'choice';

        $view = $this->factory
            ->create($name, null, array())
            ->createView();

        $this->assertTrue(isset($view->vars['sortable']));
        $this->assertFalse($view->vars['sortable']);
    }
}
