<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Extension\Event\ConfigureBatchActionsTask;
use Sonata\AdminBundle\Extension\ExtensionProcessor;
use Sonata\AdminBundle\Extension\ExtensionProvider;
use Sonata\AdminBundle\Tests\Fixtures\Admin\Extension\DummyExtension;

class ExtensionProcessorTest extends TestCase
{
    /**
     * @var ExtensionProvider
     */
    private $extensionProvider;

    /**
     * @var ExtensionProcessor
     */
    private $extensionProcessor;

    public function setUp()
    {
        $this->extensionProvider = new ExtensionProvider();
        $this->extensionProvider->addExtension(new DummyExtension());

        $this->extensionProcessor = new ExtensionProcessor($this->extensionProvider);
    }

    public function testEventExists()
    {
        $event = $this->extensionProcessor->process(
            new ConfigureBatchActionsTask(
                $this->createMock(AdminInterface::class),
                $array = ['testing']
            )
        );

        $this->assertInstanceOf(ConfigureBatchActionsTask::class, $event);
        $this->assertSame($array, $event->getActions());
    }
}
