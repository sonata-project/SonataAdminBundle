<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Widget;

use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Tests\Fixtures\TestExtension;
use Symfony\Component\HttpKernel\Kernel;

class FormSonataNativeCollectionWidgetTest extends BaseWidgetTest
{
    protected $type = 'form';

    public function setUp()
    {
        parent::setUp();
    }

    public function prototypeRenderingProvider()
    {
        return array(
            'shrinkable collection'   => array(array('allow_delete' => true)),
            'unshrinkable collection' => array(array('allow_delete' => false)),
        );
    }

    /**
     * @dataProvider prototypeRenderingProvider
     */
    public function testPrototypeIsDeletableNoMatterTheShrinkability(array $options)
    {
        $choice = $this->factory->create(
            $this->getChoiceClass(),
            null,
            array('allow_add' => true) + $options
        );

        $html = $this->renderWidget($choice->createView());

        $this->assertContains(
            'sonata-collection-delete',
            $this->cleanHtmlWhitespace($html)
        );
    }

    protected function getExtensions()
    {
        $extensions = parent::getExtensions();
        if (!version_compare(Kernel::VERSION, '2.8.0', '>=')) {
            $guesser = $this->getMock('Symfony\Component\Form\FormTypeGuesserInterface');
            $extension = new TestExtension($guesser);
            $extension->addType(new CollectionType());

            $extensions[] = $extension;
        }

        return $extensions;
    }

    protected function getChoiceClass()
    {
        if (version_compare(Kernel::VERSION, '2.8.0', '>=')) {
            return 'Sonata\AdminBundle\Form\Type\CollectionType';
        } else {
            return 'sonata_type_native_collection';
        }
    }
}
