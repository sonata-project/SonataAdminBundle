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
use Symfony\Component\Form\Forms;

class ChoiceTypeExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected function setup()
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new ChoiceTypeExtension())
            ->getFormFactory();
    }

    public function testExtendedType()
    {
        $extension = new ChoiceTypeExtension();

        $this->assertEquals('choice', $extension->getExtendedType());
    }

    public function testDefaultOptionsWithSortable()
    {
        $view = $this->factory
            ->create('choice', null, array(
                'sortable' => true,
            ))
            ->createView();

        $this->assertTrue(isset($view->vars['sortable']));
        $this->assertTrue($view->vars['sortable']);
    }

    public function testDefaultOptionsWithoutSortable()
    {
        $view = $this->factory
            ->create('choice', null, array())
            ->createView();

        $this->assertTrue(isset($view->vars['sortable']));
        $this->assertFalse($view->vars['sortable']);
    }
}
