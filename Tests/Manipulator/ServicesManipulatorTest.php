<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Generator;

use Sonata\AdminBundle\Manipulator\ServicesManipulator;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 */
class ServicesManipulatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServicesManipulator */
    private $servicesManipulator;

    /** @var string */
    private $file;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->file = sprintf('%s/%s.yml', sys_get_temp_dir(), lcg_value());
        $this->servicesManipulator = new ServicesManipulator($this->file);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        @unlink($this->file);
    }

    public function testAddResource()
    {
        $this->servicesManipulator->addResource(
            'service_id',
            'class',
            'admin_class',
            'controller_name',
            'manager_type'
        );
        $this->assertSame(
            "services:
    service_id:
        class: admin_class
        arguments: [~, class, controller_name]
        tags:
            - {name: sonata.admin, manager_type: manager_type, group: admin, label: class}\n",
            file_get_contents($this->file)
        );
        $this->servicesManipulator->addResource(
            'another_service_id',
            'another_class',
            'another_admin_class',
            'another_controller_name',
            'another_manager_type'
        );
        $this->assertSame(
            "services:
    service_id:
        class: admin_class
        arguments: [~, class, controller_name]
        tags:
            - {name: sonata.admin, manager_type: manager_type, group: admin, label: class}

    another_service_id:
        class: another_admin_class
        arguments: [~, another_class, another_controller_name]
        tags:
            - {name: sonata.admin, manager_type: another_manager_type, group: admin, label: another_class}\n",
            file_get_contents($this->file)
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The service "service_id" is already defined
     */
    public function testAddResourceShouldThrowException()
    {
        $this->servicesManipulator->addResource(
            'service_id',
            'class',
            'admin_class',
            'controller_name',
            'manager_type'
        );
        $this->servicesManipulator->addResource(
            'service_id',
            'class',
            'admin_class',
            'controller_name',
            'manager_type'
        );
    }

    public function testAddResourceWithEmptyServices()
    {
        file_put_contents($this->file, 'services:');
        $this->servicesManipulator->addResource(
            'service_id',
            'class',
            'admin_class',
            'controller_name',
            'manager_type'
        );
        $this->assertSame(
            "services:
    service_id:
        class: admin_class
        arguments: [~, class, controller_name]
        tags:
            - {name: sonata.admin, manager_type: manager_type, group: admin, label: class}\n",
            file_get_contents($this->file)
        );
    }
}
