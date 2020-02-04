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

class SearchActionTest extends TestCase
{
    private $container;
    private $pool;
    private $searchHandler;
    private $action;
    private $twig;
    private $breadcrumbsBuilder;

    protected function setUp(): void
    {
        $this->container = new Container();

        $this->pool = new Pool($this->container, 'title', 'logo.png');
        $templateRegistry = new TemplateRegistry([
            'search' => 'search.html.twig',
            'layout' => 'layout.html.twig',
        ]);

        $this->breadcrumbsBuilder = $this->createMock(BreadcrumbsBuilderInterface::class);
        $this->searchHandler = $this->createMock(SearchHandler::class);
        $this->twig = $this->prophesize(Environment::class);

        $this->action = new SearchAction(
            $this->pool,
            $this->searchHandler,
            $templateRegistry,
            $this->breadcrumbsBuilder,
            $this->twig->reveal()
        );
    }

    public function testGlobalPage(): void
    {
        $request = new Request(['q' => 'some search']);
        $this->twig->render('search.html.twig', [
            'base_template' => 'layout.html.twig',
            'breadcrumbs_builder' => $this->breadcrumbsBuilder,
            'admin_pool' => $this->pool,
            'query' => 'some search',
            'groups' => [],
        ])->willReturn(new Response());

        $this->assertInstanceOf(Response::class, ($this->action)($request));
    }

    public function testAjaxCall(): void
    {
        $admin = new CleanAdmin('code', 'class', 'controller');
        $this->container->set('foo', $admin);
        $this->pool->setAdminServiceIds(['foo']);
        $request = new Request(['admin' => 'foo']);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->assertInstanceOf(JsonResponse::class, ($this->action)($request));
    }
}
