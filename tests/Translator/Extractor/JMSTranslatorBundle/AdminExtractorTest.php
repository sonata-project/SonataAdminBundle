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
 * NEXT_MAJOR: Remove this class.
 *
 * @group legacy
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

    protected function setUp(): void
    {
        if (!interface_exists(ExtractorInterface::class)) {
            $this->markTestSkipped('JMS Translator Bundle does not exist');
        }

        $this->fooAdmin = $this->getMockForAbstractClass(AdminInterface::class);
        $this->barAdmin = $this->getMockForAbstractClass(AdminInterface::class);

        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnCallback(function (string $id): AdminInterface {
                switch ($id) {
                    case 'foo_admin':
                        return $this->fooAdmin;
                    case 'bar_admin':
                        return $this->barAdmin;
                }
            });

        $logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->pool = $this->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pool
            ->method('getAdminServiceIds')
            ->willReturn(['foo_admin', 'bar_admin']);
        $this->pool
            ->method('getContainer')
            ->willReturn($container);
        $this->pool
            ->method('getAdminGroups')
            ->willReturn(['group' => [
                'label_catalogue' => 'admin_domain',
            ]]);

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
        $this->fooAdmin
            ->method('getShow')
            ->willReturnCallback(function (): void {
                $this->assertSame('foo', $this->adminExtractor->trans('foo', [], 'foo_admin_domain'));
                $this->assertSame('foo', $this->adminExtractor->transChoice('foo', 1, [], 'foo_admin_domain'));
            });
        $this->fooAdmin
            ->method('getLabel')
            ->willReturn('foo_label');
        $this->fooAdmin
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
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Foo throws exception');

        $this->fooAdmin
            ->method('getShow')
            ->willReturnCallback(static function (): void {
                throw new \RuntimeException('Foo throws exception');
            });

        $this->adminExtractor->extract();
    }

    public function testExtractCallsBreadcrumbs(): void
    {
        $this->breadcrumbsBuilder->expects($this->exactly(2 * 6))
            ->method('getBreadcrumbs');
        $this->adminExtractor->extract();
    }
}
