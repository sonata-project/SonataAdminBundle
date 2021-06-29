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

namespace Sonata\AdminBundle\Tests\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Action\SearchAction;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Search\SearchHandler;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Sonata\AdminBundle\Tests\Fixtures\Admin\CleanAdmin;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class SearchActionTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var AdminFetcherInterface&MockObject
     */
    private $adminFetcherInterface;

    /**
     * @var SearchHandler
     */
    private $searchHandler;

    /**
     * @var SearchAction
     */
    private $action;

    /**
     * @var Stub&Environment
     */
    private $twig;

    /**
     * NEXT_MAJOR: Remove this property.
     *
     * @var Stub&BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->pool = new Pool($this->container, ['foo']);
        $this->adminFetcherInterface = $this->createMock(AdminFetcherInterface::class);

        $templateRegistry = new TemplateRegistry([
            'search' => 'search.html.twig',
            'layout' => 'layout.html.twig',
        ]);

        $this->breadcrumbsBuilder = $this->createStub(BreadcrumbsBuilderInterface::class);
        $this->searchHandler = new SearchHandler(true);
        $this->twig = $this->createStub(Environment::class);

        $this->action = new SearchAction(
            $this->pool,
            $this->searchHandler,
            $templateRegistry,
            // NEXT_MAJOR: Remove next line.
            $this->breadcrumbsBuilder,
            $this->twig,
            $this->adminFetcherInterface
        );
    }

    public function testGlobalPage(): void
    {
        $request = new Request(['q' => 'some search']);
        $this->twig->method('render')->with('search.html.twig', [
            'base_template' => 'layout.html.twig',
            // NEXT_MAJOR: Remove next line.
            'breadcrumbs_builder' => $this->breadcrumbsBuilder,
            // NEXT_MAJOR: Remove next line.
            'admin_pool' => $this->pool,
            'query' => 'some search',
            'groups' => [],
        ])->willReturn('rendered_search');

        $this->assertInstanceOf(Response::class, ($this->action)($request));
    }

    public function testAjaxCall(): void
    {
        $adminCode = 'code';

        $this->searchHandler->configureAdminSearch([$adminCode => false]);
        $admin = new CleanAdmin($adminCode, 'class', 'controller');
        $this->container->set('foo', $admin);

        $this->adminFetcherInterface
            ->expects($this->once())
            ->method('get')
            ->willReturn($admin);

        $request = new Request(['_sonata_admin' => 'foo']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertInstanceOf(JsonResponse::class, ($this->action)($request));
    }
}
