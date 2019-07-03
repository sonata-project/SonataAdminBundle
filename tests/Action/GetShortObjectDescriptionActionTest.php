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
use Prophecy\Argument;
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class GetShortObjectDescriptionActionTest extends TestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var GetShortObjectDescriptionAction
     */
    private $action;

    /**
     * @var AbstractAdmin
     */
    private $admin;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader(['template' => 'renderedTemplate']));
        $this->pool = $this->prophesize(Pool::class);
        $this->admin = $this->prophesize(AbstractAdmin::class);
        $this->pool->getInstance(Argument::any())->willReturn($this->admin->reveal());
        $this->admin->setRequest(Argument::type(Request::class))->shouldBeCalled();
        $this->action = new GetShortObjectDescriptionAction(
            $this->twig,
            $this->pool->reveal(),
            $this->prophesize(TemplateRegistryInterface::class)->reveal()
        );
    }

    public function testGetShortObjectDescriptionActionInvalidAdmin(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->pool->getInstance('sonata.post.admin')->willReturn(null);
        $this->admin->setRequest(Argument::type(Request::class))->shouldNotBeCalled();

        $action = $this->action;
        $action($request);
    }

    public function testGetShortObjectDescriptionActionObjectDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid format');

        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(42)->willReturn(false);

        $action = $this->action;
        $action($request);
    }

    public function testGetShortObjectDescriptionActionEmptyObjectId(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => '',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(null)->willReturn(false);

        $action = $this->action;
        $response = $action($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testGetShortObjectDescriptionActionObject(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);
        $object = new \stdClass();

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(42)->willReturn($object);

        // mock doesn't work
        $templateRegistry = $this->createMock(MutableTemplateRegistryInterface::class);
        $templateRegistry
            ->method('getTemplate')
            ->will($this->returnValueMap(['short_object_description', 'template']));

        $this->admin->reveal()->setTemplateRegistry($templateRegistry);

        $this->admin->toString($object)->willReturn('bar');

        $action = $this->action;
        $response = $action($request);

        $this->assertSame('renderedTemplate', $response->getContent());
    }

    public function testGetShortObjectDescriptionActionEmptyObjectIdAsJson(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => '',
            'uniqid' => 'asdasd123',
            '_format' => 'json',
        ]);

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(null)->willReturn(false);
        $this->admin->id(false)->willReturn('');
        $this->admin->toString(false)->willReturn('');

        $action = $this->action;
        $response = $action($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame('{"result":{"id":"","label":""}}', $response->getContent());
    }

    public function testGetShortObjectDescriptionActionObjectAsJson(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'json',
        ]);
        $object = new \stdClass();

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->id($object)->willReturn(42);
        $this->admin->getObject(42)->willReturn($object);
        $this->admin->getTemplate('short_object_description')->willReturn('template');
        $this->admin->toString($object)->willReturn('bar');

        $action = $this->action;
        $response = $action($request);

        $this->assertSame('{"result":{"id":42,"label":"bar"}}', $response->getContent());
    }
}
