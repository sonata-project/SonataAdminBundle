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

namespace Sonata\AdminBundle\Tests\Translator\Extractor\JMSTranslatorBundle;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Translator\Extractor\JMSTranslatorBundle\AdminExtractor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test for AdminExtractor.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class AdminExtractorTest extends TestCase
{
    /**
     * @var AdminExtractor
     */
    private $adminExtractor;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var AdminInterface
     */
    private $fooAdmin;

    /**
     * @var AdminInterface
     */
    private $barAdmin;

    /**
     * @var BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    public function setUp(): void
    {
        if (!interface_exists(ExtractorInterface::class)) {
            $this->markTestSkipped('JMS Translator Bundle does not exist');
        }

        $this->fooAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $this->barAdmin = $this->getMockForAbstractClass(AdminInterface::class);

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) {
                switch ($id) {
                    case 'foo_admin':
                        return $this->fooAdmin;
                    case 'bar_admin':
                        return $this->barAdmin;
                }
            }));

        $logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->pool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pool->expects($this->any())
            ->method('getAdminServiceIds')
            ->will($this->returnValue(['foo_admin', 'bar_admin']));
        $this->pool->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($container));
        $this->pool->expects($this->any())
            ->method('getAdminGroups')
            ->will($this->returnValue(['group' => [
                'label_catalogue' => 'admin_domain',
            ]]));

        $this->adminExtractor = new AdminExtractor($this->pool, $logger);
        $this->adminExtractor->setLogger($logger);

        $this->breadcrumbsBuilder = $this->getMockForAbstractClass(BreadcrumbsBuilderInterface::class);
        $this->adminExtractor->setBreadcrumbsBuilder($this->breadcrumbsBuilder);
    }

    public function testExtractEmpty(): void
    {
        $catalogue = $this->adminExtractor->extract();

        $this->assertInstanceOf(MessageCatalogue::class, $catalogue);
        $this->assertFalse($catalogue->has(new Message('foo', 'foo_admin_domain')));
    }

    public function testExtract(): void
    {
        $this->fooAdmin->expects($this->any())
            ->method('getShow')
            ->will($this->returnCallback(function (): void {
                $this->assertEquals('foo', $this->adminExtractor->trans('foo', [], 'foo_admin_domain'));
                $this->assertEquals('foo', $this->adminExtractor->transChoice('foo', 1, [], 'foo_admin_domain'));
            }));
        $this->fooAdmin->expects($this->any())
            ->method('getLabel')
            ->willReturn('foo_label');
        $this->fooAdmin->expects($this->any())
            ->method('getTranslationDomain')
            ->willReturn('foo_admin_domain');

        $catalogue = $this->adminExtractor->extract();

        $this->assertCount(2, $catalogue->getDomains());

        $this->assertTrue($catalogue->has(new Message('foo', 'foo_admin_domain')));
        $this->assertFalse($catalogue->has(new Message('nonexistent', 'foo_admin_domain')));

        $this->assertInstanceOf(Message::class, $catalogue->get('foo', 'foo_admin_domain'));

        $message = $catalogue->get('foo', 'foo_admin_domain');
        $this->assertSame('foo', $message->getId());
        $this->assertSame('foo_admin_domain', $message->getDomain());

        $this->assertTrue($catalogue->has(new Message('group', 'admin_domain')));
        $this->assertTrue($catalogue->has(new Message('foo_label', 'foo_admin_domain')));
    }

    public function testExtractWithException(): void
    {
        $this->expectException(\RuntimeException::class, 'Foo throws exception');

        $this->fooAdmin->expects($this->any())
            ->method('getShow')
            ->will($this->returnCallback(function (): void {
                throw new \RuntimeException('Foo throws exception');
            }));

        $this->adminExtractor->extract();
    }

    public function testExtractCallsBreadcrumbs(): void
    {
        $this->breadcrumbsBuilder->expects($this->exactly(2 * 6))
            ->method('getBreadcrumbs');
        $this->adminExtractor->extract();
    }
}
