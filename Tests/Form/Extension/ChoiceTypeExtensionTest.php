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

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Form\Extension\ChoiceTypeExtension;
use Sonata\CoreBundle\Form\Extension\DependencyInjectionExtension;
use Symfony\Component\Form\Forms;

class ChoiceTypeExtensionTest extends TestCase
{
    protected function setup()
    {
        $container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())->method('has')->will($this->returnValue(true));
        $container->expects($this->any())->method('get')
            ->with($this->equalTo('sonata.admin.form.choice_extension'))
            ->will($this->returnValue(new ChoiceTypeExtension()));

        $typeServiceIds = [];
        $typeExtensionServiceIds = [];
        $guesserServiceIds = [];
        $mappingTypes = [
            'choice' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        ];
        $extensionTypes = [
            'choice' => [
                'sonata.admin.form.choice_extension',
            ],
        ];

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
    }

    public function testExtendedType()
    {
        $extension = new ChoiceTypeExtension();

        $this->assertSame(
            'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            $extension->getExtendedType()
        );
    }

    public function testDefaultOptionsWithSortable()
    {
        $name = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';

        $view = $this->factory
            ->create($name, null, [
                'sortable' => true,
            ])
            ->createView();

        $this->assertTrue(isset($view->vars['sortable']));
        $this->assertTrue($view->vars['sortable']);
    }

    public function testDefaultOptionsWithoutSortable()
    {
        $name = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';

        $view = $this->factory
            ->create($name, null, [])
            ->createView();

        $this->assertTrue(isset($view->vars['sortable']));
        $this->assertFalse($view->vars['sortable']);
    }
}
