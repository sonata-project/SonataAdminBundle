<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use JMS\DiExtraBundle\Metadata\ClassMetadata;
use Sonata\AdminBundle\Annotation\Admin;

class AnnotationCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidAdminAnnotation()
    {
        /*
         * @Admin(class="Sonata\AdminBundle\Tests\Fixtures\Foo")
         */

        $this->setExpectedException(
            'LogicException',
            'Unable to generate admin group and label for class Sonata\AdminBundle\Tests\Fixtures\Foo.'
        );

        $annotation = new Admin();
        $annotation->class = 'Sonata\AdminBundle\Tests\Fixtures\Foo';

        $meta = new ClassMetadata('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo');

        $annotation->processMetadata($meta);
    }

    public function testEmbeddedAdmin()
    {
        /*
         * @Admin(
         *   class="Sonata\Admin\Entity\Tests\Fixtures\Foo",
         *   showInDashboard=false
         * )
         */
        $annotation = new Admin();
        $annotation->class = 'Sonata\Admin\Entity\Tests\Fixtures\Foo';
        $annotation->showInDashboard = false;

        $meta = new ClassMetadata('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo');

        $annotation->processMetadata($meta);

        $this->assertSame(
            $meta->tags['sonata.admin'][0],
            array(
                'manager_type'      => 'orm',
                'group'             => 'Admin',
                'label'             => 'Tests\Fixtures\Foo',
                'show_in_dashboard' => false,
            )
        );
    }

    public function testMinimalAdmin()
    {
        /*
         * @Admin(class="Sonata\AdminBundle\Entity\Foo")
         */
        $annotation = new Admin();
        $annotation->class = 'Sonata\AdminBundle\Entity\Foo';

        $meta = new ClassMetadata('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo');

        $annotation->processMetadata($meta);

        $this->assertSame(
            $meta->tags['sonata.admin'][0],
            array(
                'manager_type'      => 'orm',
                'group'             => 'Admin',
                'label'             => 'Foo',
                'show_in_dashboard' => true,
            )
        );
    }

    public function testAdmin()
    {
        /*
         * @Admin(
         *      class="Sonata\AdminBundle\Entity\Foo",
         *      managerType="doctrine_mongodb",
         *      group="myGroup",
         *      label="myLabel",
         *      translationDomain="OMG"
         * )
         */
        $annotation = new Admin();
        $annotation->class = 'Sonata\AdminBundle\Entity\Foo';
        $annotation->managerType = 'doctrine_mongodb';
        $annotation->group = 'myGroup';
        $annotation->label = 'myLabel';
        $annotation->showInDashboard = false;
        $annotation->translationDomain = 'OMG';

        $meta = new ClassMetadata('Sonata\AdminBundle\Tests\Fixtures\Entity\Foo');

        $annotation->processMetadata($meta);

        $this->assertSame(
            $meta->tags['sonata.admin'][0],
            array(
                'manager_type'      => 'doctrine_mongodb',
                'group'             => 'myGroup',
                'label'             => 'myLabel',
                'show_in_dashboard' => false,
            )
        );

        $this->assertSame($meta->methodCalls[0], array('setTranslationDomain', array('OMG')));
    }
}
