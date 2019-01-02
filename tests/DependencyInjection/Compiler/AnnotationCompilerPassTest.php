<?php

declare(strict_types=1);

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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Annotation\Admin;

class AnnotationCompilerPassTest extends TestCase
{
    public function testInvalidAdminAnnotation(): void
    {
        /*
         * @Admin(class="Sonata\AdminBundle\Tests\Fixtures\Foo")
         */

        $this->expectException(
            \LogicException::class,
            'Unable to generate admin group and label for class Sonata\AdminBundle\Tests\Fixtures\Foo.'
        );

        $annotation = new Admin();
        $annotation->class = \Sonata\AdminBundle\Tests\Fixtures\Foo::class;

        $meta = new ClassMetadata(\Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class);

        $annotation->processMetadata($meta);
    }

    public function testEmbeddedAdmin(): void
    {
        /*
         * @Admin(
         *   class="Sonata\Admin\Entity\Tests\Fixtures\Foo",
         *   showInDashboard=false
         * )
         */
        $annotation = new Admin();
        $annotation->class = \Sonata\Admin\Entity\Tests\Fixtures\Foo::class;
        $annotation->showInDashboard = false;

        $meta = new ClassMetadata(\Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class);

        $annotation->processMetadata($meta);

        $this->assertSame(
            [
                'manager_type' => 'orm',
                'group' => 'Admin',
                'label' => 'Tests\Fixtures\Foo',
                'show_in_dashboard' => false,
                'keep_open' => false,
                'on_top' => false,
            ],
            $meta->tags['sonata.admin'][0]
        );
    }

    public function testMinimalAdmin(): void
    {
        /*
         * @Admin(class="Sonata\AdminBundle\Entity\Foo")
         */
        $annotation = new Admin();
        $annotation->class = \Sonata\AdminBundle\Entity\Foo::class;

        $meta = new ClassMetadata(\Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class);

        $annotation->processMetadata($meta);

        $this->assertSame(
            [
                'manager_type' => 'orm',
                'group' => 'Admin',
                'label' => 'Foo',
                'show_in_dashboard' => true,
                'keep_open' => false,
                'on_top' => false,
            ],
            $meta->tags['sonata.admin'][0]
        );
    }

    public function testIdForAdmin(): void
    {
        /*
         * @Admin(class="Sonata\AdminBundle\Entity\Foo", id="my.id")
         */
        $annotation = new Admin();
        $annotation->class = \Sonata\AdminBundle\Entity\Foo::class;
        $annotation->id = 'my.id';

        $meta = new ClassMetadata(\Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class);

        $annotation->processMetadata($meta);

        $this->assertSame('my.id', $meta->id);
    }

    public function testAdmin(): void
    {
        /*
         * @Admin(
         *      class="Sonata\AdminBundle\Entity\Foo",
         *      managerType="doctrine_mongodb",
         *      group="myGroup",
         *      label="myLabel",
         *      translationDomain="OMG",
         *      keepOpen=true,
         *      onTop=true
         * )
         */
        $annotation = new Admin();
        $annotation->class = \Sonata\AdminBundle\Entity\Foo::class;
        $annotation->managerType = 'doctrine_mongodb';
        $annotation->group = 'myGroup';
        $annotation->label = 'myLabel';
        $annotation->showInDashboard = false;
        $annotation->translationDomain = 'OMG';
        $annotation->keepOpen = true;
        $annotation->onTop = true;

        $meta = new ClassMetadata(\Sonata\AdminBundle\Tests\Fixtures\Entity\Foo::class);

        $annotation->processMetadata($meta);

        $this->assertSame(
            [
                'manager_type' => 'doctrine_mongodb',
                'group' => 'myGroup',
                'label' => 'myLabel',
                'show_in_dashboard' => false,
                'keep_open' => true,
                'on_top' => true,
            ],
            $meta->tags['sonata.admin'][0]
        );

        $this->assertSame(['setTranslationDomain', ['OMG']], $meta->methodCalls[0]);
    }
}
