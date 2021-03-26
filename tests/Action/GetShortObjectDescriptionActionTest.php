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
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Templating\MutableTemplateRegistry;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
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
     * @var AdminInterface&MockObject
     */
    private $admin;

    /**
     * @var string
     */
    private $adminCode;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader(['short_object_description' => 'renderedTemplate']));
        $this->adminCode = 'sonata.post.admin';
        $this->admin = $this->createMock(AdminInterface::class);
        $container = new Container();
        $container->set($this->adminCode, $this->admin);
        $this->pool = new Pool($container, [$this->adminCode]);

        $this->action = new GetShortObjectDescriptionAction(
            $this->twig,
            $this->pool
        );
    }

    public function testGetShortObjectDescriptionActionInvalidAdmin(): void
    {
        $request = new Request([
            'code' => 'non_existing_code',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->admin->expects($this->never())->method('setRequest');

        $this->expectException(NotFoundHttpException::class);

        ($this->action)($request);
    }

    public function testGetShortObjectDescriptionActionObjectDoesNotExist(): void
    {
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->admin->expects($this->once())->method('setRequest')->with($request);
        $this->admin->expects($this->once())->method('setUniqid')->with('asdasd123');
        $this->admin->method('getObject')->with(42)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        ($this->action)($request);
    }

    public function testGetShortObjectDescriptionActionEmptyObjectId(): void
    {
        $request = new Request([
            'code' => $this->adminCode,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $this->admin->expects($this->once())->method('setRequest')->with($request);
        $this->admin->expects($this->once())->method('setUniqid')->with('asdasd123');
        $this->admin->method('getObject')->with(null)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        ($this->action)($request);
    }

    public function testGetShortObjectDescriptionActionObject(): void
    {
        $templateRegistry = new MutableTemplateRegistry([
            'short_object_description' => 'short_object_description',
        ]);

        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);
        $object = new \stdClass();

        $this->admin->expects($this->once())->method('setRequest')->with($request);
        $this->admin->expects($this->once())->method('setUniqid')->with('asdasd123');
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('toString')->with($object)->willReturn('bar');
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('getTemplateRegistry')->willReturn($templateRegistry);

        $response = ($this->action)($request);

        $this->assertSame('renderedTemplate', $response->getContent());
    }

    public function testGetShortObjectDescriptionActionEmptyObjectIdAsJson(): void
    {
        $request = new Request([
            'code' => $this->adminCode,
            'uniqid' => 'asdasd123',
            '_format' => 'json',
        ]);

        $this->admin->expects($this->once())->method('setRequest')->with($request);
        $this->admin->expects($this->once())->method('setUniqid')->with('asdasd123');
        $this->admin->method('getObject')->with(null)->willReturn(null);
        $this->admin->method('id')->with(null)->willReturn('');
        $this->admin->method('toString')->with(null)->willReturn('');

        $this->expectException(NotFoundHttpException::class);
        ($this->action)($request);
    }

    public function testGetShortObjectDescriptionActionObjectAsJson(): void
    {
        $request = new Request([
            'code' => $this->adminCode,
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'json',
        ]);
        $object = new \stdClass();

        $this->admin->expects($this->once())->method('setRequest')->with($request);
        $this->admin->expects($this->once())->method('setUniqid')->with('asdasd123');
        $this->admin->method('id')->with($object)->willReturn('42');
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('toString')->with($object)->willReturn('bar');

        $response = ($this->action)($request);

        $this->assertSame('{"result":{"id":"42","label":"bar"}}', $response->getContent());
    }
}
