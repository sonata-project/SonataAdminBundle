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

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Action\SearchAction;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Admin\Pool;
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
     * @var Stub&BreadcrumbsBuilderInterface
     */
    private $breadcrumbsBuilder;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->pool = new Pool($this->container, ['foo']);

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
            $this->breadcrumbsBuilder,
            $this->twig
        );
    }

    public function testGlobalPage(): void
    {
        $request = new Request(['q' => 'some search']);
        $this->twig->method('render')->with('search.html.twig', [
            'base_template' => 'layout.html.twig',
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
        $request = new Request(['admin' => 'foo']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertInstanceOf(JsonResponse::class, ($this->action)($request));
    }
}
