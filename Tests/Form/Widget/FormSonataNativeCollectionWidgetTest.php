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

use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Tests\Fixtures\TestExtension;

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
        $guesser = $this->getMock('Symfony\Component\Form\FormTypeGuesserInterface');
        $extension = new TestExtension($guesser);
        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $extension->addType(new CollectionType());
        }

        $extension->addTypeExtension(new FormTypeFieldExtension(array(), array(
            'form_type' => 'vertical',
        )));
        $extensions[] = $extension;

        return $extensions;
    }

    protected function getChoiceClass()
    {
        return
            method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ?
            'Sonata\AdminBundle\Form\Type\CollectionType' :
            'sonata_type_native_collection';
    }
}
