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

namespace Sonata\AdminBundle\Tests\Manipulator;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Manipulator\ServicesManipulator;

/**
 * @author Marek Stipek <mario.dweller@seznam.cz>
 */
final class ServicesManipulatorTest extends TestCase
{
    private ServicesManipulator $servicesManipulator;

    private string $file;

    protected function setUp(): void
    {
        $this->file = \sprintf('%s/%s.yml', sys_get_temp_dir(), lcg_value());
        $this->servicesManipulator = new ServicesManipulator($this->file);
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testAddResource(): void
    {
        $this->servicesManipulator->addResource(
            'service_id',
            \stdClass::class,
            AdminInterface::class,
            'controller_name',
            'manager_type'
        );
        static::assertSame(
            "services:
    service_id:
        class: Sonata\AdminBundle\Admin\AdminInterface
        tags:
            - { name: sonata.admin, model_class: stdClass, controller: controller_name, manager_type: manager_type, group: admin, label: stdClass }\n",
            file_get_contents($this->file)
        );
        $this->servicesManipulator->addResource(
            'another_service_id',
            \stdClass::class,
            AdminInterface::class,
            'another_controller_name',
            'another_manager_type'
        );
        static::assertSame(
            "services:
    service_id:
        class: Sonata\AdminBundle\Admin\AdminInterface
        tags:
            - { name: sonata.admin, model_class: stdClass, controller: controller_name, manager_type: manager_type, group: admin, label: stdClass }

    another_service_id:
        class: Sonata\AdminBundle\Admin\AdminInterface
        tags:
            - { name: sonata.admin, model_class: stdClass, controller: another_controller_name, manager_type: another_manager_type, group: admin, label: stdClass }\n",
            file_get_contents($this->file)
        );
    }

    public function testAddResourceShouldThrowException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The service "service_id" is already defined');

        $this->servicesManipulator->addResource(
            'service_id',
            \stdClass::class,
            AdminInterface::class,
            'controller_name',
            'manager_type'
        );
        $this->servicesManipulator->addResource(
            'service_id',
            \stdClass::class,
            AdminInterface::class,
            'controller_name',
            'manager_type'
        );
    }

    public function testAddResourceWithEmptyServices(): void
    {
        file_put_contents($this->file, 'services:');
        $this->servicesManipulator->addResource(
            'service_id',
            \stdClass::class,
            AdminInterface::class,
            'controller_name',
            'manager_type'
        );
        static::assertSame(
            "services:
    service_id:
        class: Sonata\AdminBundle\Admin\AdminInterface
        tags:
            - { name: sonata.admin, model_class: stdClass, controller: controller_name, manager_type: manager_type, group: admin, label: stdClass }\n",
            file_get_contents($this->file)
        );
    }
}
