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
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Action\SearchAction;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Symfony\Component\DependencyInjection\Container;
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
     * @var SearchAction
     */
    private $action;

    /**
     * @var MockObject&Environment
     */
    private $twig;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->pool = new Pool($this->container, ['foo']);
        $templateRegistry = new TemplateRegistry([
            'search' => 'search.html.twig',
            'layout' => 'layout.html.twig',
        ]);

        $this->twig = $this->createMock(Environment::class);

        $this->action = new SearchAction(
            $this->pool,
            $templateRegistry,
            $this->twig
        );
    }

    public function testGlobalPage(): void
    {
        $request = new Request(['q' => 'some search']);
        $this->twig->method('render')->with('search.html.twig', [
            'base_template' => 'layout.html.twig',
            'query' => 'some search',
            'groups' => [],
        ])->willReturn('rendered_search');

        self::assertInstanceOf(Response::class, ($this->action)($request));
    }
}
