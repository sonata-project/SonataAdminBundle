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

namespace Sonata\AdminBundle\Tests\Translator\Extractor;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Translator\Extractor\AdminExtractor;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Translation\MessageCatalogue;

final class AdminExtractorTest extends TestCase
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
        $this->fooAdmin = $this->createStub(AdminInterface::class);
        $this->barAdmin = $this->createStub(AdminInterface::class);

        $container = new Container();
        $container->set('foo_admin', $this->fooAdmin);
        $container->set('bar_admin', $this->barAdmin);

        $this->pool = new Pool($container, 'title', 'logo_title');
        $this->pool->setAdminServiceIds(['foo_admin', 'bar_admin']);
        $this->pool->setAdminGroups(['group' => [
            'label_catalogue' => 'admin_domain',
        ]]);

        $this->breadcrumbsBuilder = $this->createMock(BreadcrumbsBuilderInterface::class);
        $this->adminExtractor = new AdminExtractor($this->pool, $this->breadcrumbsBuilder);
    }

    public function testExtractEmpty(): void
    {
        $catalogue = new MessageCatalogue('en');

        $this->adminExtractor->extract([], $catalogue);
        $this->assertFalse($catalogue->has('foo', 'foo_admin_domain'));
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

        $this->assertCount(2, $catalogue->getDomains());
        $message = $catalogue->get('foo', 'foo_admin_domain');
        $this->assertSame('foo', $message);

        $this->assertTrue($catalogue->has('group', 'admin_domain'));
        $this->assertTrue($catalogue->has('foo_label', 'foo_admin_domain'));
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
        $numberOfAdmins = \count($this->pool->getAdminServiceIds());
        $numberOfActionsToCheck = 6;

        $this->breadcrumbsBuilder->expects($this->exactly($numberOfAdmins * $numberOfActionsToCheck))
            ->method('getBreadcrumbs');
        $catalogue = new MessageCatalogue('en');

        $this->adminExtractor->extract([], $catalogue);
    }
}
