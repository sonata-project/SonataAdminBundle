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
            $this->pool->reveal()
        );
    }

    public function testGetShortObjectDescriptionActionInvalidAdmin(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $code = 'sonata.post.admin';

        $request = new Request([
            'code' => $code,
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->pool->getInstance($code)->willThrow(\InvalidArgumentException::class);
        $this->admin->setRequest(Argument::type(Request::class))->shouldNotBeCalled();

        ($this->action)($request);
    }

    /**
     * NEXT_MAJOR: Expect a NotFoundHttpException instead.
     *
     * @group legacy
     * @expectedDeprecation Trying to get a short object description for a non found object is deprecated since sonata-project/admin-bundle 3.x and will be throw a 404 in version 4.0.
     */
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

        ($this->action)($request);
    }

    /**
     * NEXT_MAJOR: Expect a NotFoundHttpException instead.
     *
     * @group legacy
     * @expectedDeprecation Trying to get a short object description for a non found object is deprecated since sonata-project/admin-bundle 3.x and will be throw a 404 in version 4.0.
     */
    public function testGetShortObjectDescriptionActionEmptyObjectId(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(null)->willReturn(null);

        $this->assertInstanceOf(Response::class, ($this->action)($request));
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
        $this->admin->getTemplate('short_object_description')->willReturn('template');
        $this->admin->toString($object)->willReturn('bar');

        $response = ($this->action)($request);

        $this->assertSame('renderedTemplate', $response->getContent());
    }

    /**
     * NEXT_MAJOR: Expect a NotFoundHttpException instead.
     *
     * @group legacy
     * @expectedDeprecation Trying to get a short object description for a non found object is deprecated since sonata-project/admin-bundle 3.x and will be throw a 404 in version 4.0.
     */
    public function testGetShortObjectDescriptionActionEmptyObjectIdAsJson(): void
    {
        $request = new Request([
            'code' => 'sonata.post.admin',
            'uniqid' => 'asdasd123',
            '_format' => 'json',
        ]);

        $this->admin->setUniqid('asdasd123')->shouldBeCalled();
        $this->admin->getObject(null)->willReturn(null);
        $this->admin->id(null)->willReturn('');
        $this->admin->toString(null)->willReturn('');

        $response = ($this->action)($request);

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

        $response = ($this->action)($request);

        $this->assertSame('{"result":{"id":42,"label":"bar"}}', $response->getContent());
    }
}
