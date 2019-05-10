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

namespace Sonata\AdminBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Event\ConfigureFilterParametersEvent;
use Sonata\AdminBundle\Filter\FilterBag;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class ConfigureFilterParametersEventTest extends TestCase
{
    /**
     * @var ConfigureFilterParametersEvent
     */
    private $event;

    /**
     * @var AdminInterface
     */
    private $admin;

    /**
     * @var FilterBag
     */
    private $filterBag;

    protected function setUp(): void
    {
        $this->admin = $this->getMockBuilder(AbstractAdmin::class)
                    ->disableOriginalConstructor()
                    ->disableOriginalClone()
                    ->disableArgumentCloning()
                    ->disallowMockingUnknownTypes()
                    ->setMethodsExcept(['getFilterParameters', 'getExtensions'])
                    ->getMockForAbstractClass();

        $this->filterBag = new FilterBag();

        $this->event = new ConfigureFilterParametersEvent($this->admin, $this->filterBag);
    }

    public function testGetAdmin(): void
    {
        $result = $this->event->getAdmin();

        $this->assertInstanceOf(AdminInterface::class, $result);
        $this->assertSame($this->admin, $result);
    }

    public function testGetFilterBag(): void
    {
        $result = $this->event->getFilterBag();

        $this->assertInstanceOf(FilterBag::class, $result);
        $this->assertSame($this->filterBag, $result);
    }

    public function testAdminGetFilterParameters(): void
    {
        $modelManager = $this->getMockForAbstractClass(ModelManagerInterface::class);
        $modelManager->method('getDefaultSortValues')
             ->willReturn(['some_filter' => 'another_value']);

        $this->admin->method('getModelManager')
             ->willReturn($modelManager);

        $result = $this->event->getFilterBag();

        \Closure::bind(function (FilterBag $filterBag): void {
            $this->filterBag = $filterBag;
        }, $this->admin, AbstractAdmin::class)($result);

        $this->assertArrayHasKey('some_filter', $this->admin->getFilterParameters());
        $this->assertSame('another_value', $this->admin->getFilterParameters()['some_filter']);

        $result->set('some_filter', 'some_value');

        $this->assertArrayHasKey('some_filter', $this->admin->getFilterParameters());
        $this->assertSame('some_value', $this->admin->getFilterParameters()['some_filter']);
    }
}
