<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Translator\Extractor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use SensioLabs\AdminBundle\FieldDescription\FieldDescriptionCollection;
use SensioLabs\AdminBundle\Translator\Extractor\AdminExtractor;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Translation\MessageCatalogue;

final class AdminExtractorTest extends TestCase
{
    private AdminExtractor $adminExtractor;

    private Pool $pool;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $fooAdmin;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $barAdmin;

    /**
     * @var BreadcrumbsBuilderInterface&MockObject
     */
    private BreadcrumbsBuilderInterface $breadcrumbsBuilder;

    protected function setUp(): void
    {
        $this->fooAdmin = $this->createMock(AdminInterface::class);
        $this->barAdmin = $this->createMock(AdminInterface::class);

        $this->fooAdmin->method('getShow')->willReturn(new FieldDescriptionCollection());
        $this->fooAdmin->method('getList')->willReturn(new FieldDescriptionCollection());
        $this->fooAdmin
            ->method('getClass')
            ->willReturn(\stdClass::class);
        $this->barAdmin->method('getShow')->willReturn(new FieldDescriptionCollection());
        $this->barAdmin->method('getList')->willReturn(new FieldDescriptionCollection());
        $this->barAdmin
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $container = new Container();
        $container->set('foo_admin', $this->fooAdmin);
        $container->set('bar_admin', $this->barAdmin);

        $this->pool = new Pool($container, ['foo_admin', 'bar_admin']);

        $this->breadcrumbsBuilder = $this->createMock(BreadcrumbsBuilderInterface::class);
        $this->adminExtractor = new AdminExtractor($this->pool, $this->breadcrumbsBuilder);
    }

    public function testExtractEmpty(): void
    {
        $catalogue = new MessageCatalogue('en');

        $this->adminExtractor->extract([], $catalogue);
        static::assertFalse($catalogue->has('foo', 'foo_admin_domain'));
    }

    public function testExtract(): void
    {
        $this->fooAdmin
            ->method('getLabel')
            ->willReturn('foo_label');
        $this->fooAdmin
            ->method('getTranslationDomain')
            ->willReturn('foo_admin_domain');

        $catalogue = new MessageCatalogue('en');

        $this->adminExtractor->extract([], $catalogue);

        static::assertCount(1, $catalogue->getDomains());

        static::assertTrue($catalogue->has('foo_label', 'foo_admin_domain'));
    }

    public function testExtractWithException(): void
    {
        $this->fooAdmin
            ->method('getShow')
            ->willThrowException(new \RuntimeException('Foo throws exception'));

        $catalogue = new MessageCatalogue('en');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Foo throws exception');

        $this->adminExtractor->extract([], $catalogue);
    }

    public function testExtractCallsBreadcrumbs(): void
    {
        $numberOfAdmins = \count($this->pool->getAdminServiceCodes());
        $numberOfActionsToCheck = 6;

        $this->breadcrumbsBuilder->expects(static::exactly($numberOfAdmins * $numberOfActionsToCheck))
            ->method('getBreadcrumbs');
        $catalogue = new MessageCatalogue('en');

        $this->adminExtractor->extract([], $catalogue);
    }

    public function testExtractSetsSubject(): void
    {
        $this->fooAdmin
            ->expects(static::exactly(1))
            ->method('setSubject');
        $this->fooAdmin
            ->expects(static::exactly(1))
            ->method('getNewInstance');

        $catalogue = new MessageCatalogue('en');

        $this->adminExtractor->extract([], $catalogue);
    }
}
