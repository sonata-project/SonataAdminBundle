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
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class GetShortObjectDescriptionActionTest extends TestCase
{
    /**
     * @var Stub&AdminFetcherInterface
     */
    private $adminFetcher;

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
        $this->admin = $this->createMock(AbstractAdmin::class);
        $this->adminFetcher = $this->createStub(AdminFetcherInterface::class);
        $this->action = new GetShortObjectDescriptionAction(
            $this->twig,
            $this->adminFetcher
        );
    }

    public function testGetShortObjectDescriptionActionInvalidAdmin(): void
    {
        $request = new Request([
            '_sonata_admin' => 'non_existing_code',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->adminFetcher->method('get')->willThrowException(new \InvalidArgumentException());

        $this->admin->expects($this->never())->method('setRequest');

        $this->expectException(NotFoundHttpException::class);

        ($this->action)($request);
    }

    /**
     * NEXT_MAJOR: Expect a NotFoundHttpException instead.
     *
     * @group legacy
     * @expectedDeprecation Trying to get a short object description for a non found object is deprecated since sonata-project/admin-bundle 3.76 and will be throw a 404 in version 4.0.
     */
    public function testGetShortObjectDescriptionActionObjectDoesNotExist(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid format');

        $this->adminFetcher->method('get')->willReturn($this->admin);

        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->admin->method('getObject')->with(42)->willReturn(null);

        ($this->action)($request);
    }

    /**
     * NEXT_MAJOR: Expect a NotFoundHttpException instead.
     *
     * @group legacy
     * @expectedDeprecation Trying to get a short object description for a non found object is deprecated since sonata-project/admin-bundle 3.76 and will be throw a 404 in version 4.0.
     */
    public function testGetShortObjectDescriptionActionEmptyObjectId(): void
    {
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $this->adminFetcher->method('get')->willReturn($this->admin);

        $this->admin->method('getObject')->with(null)->willReturn(null);

        $this->assertInstanceOf(Response::class, ($this->action)($request));
    }

    public function testGetShortObjectDescriptionActionObject(): void
    {
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);
        $object = new \stdClass();

        $this->adminFetcher->method('get')->willReturn($this->admin);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getTemplate')->with('short_object_description')->willReturn('template');
        $this->admin->method('toString')->with($object)->willReturn('bar');

        $response = ($this->action)($request);

        $this->assertSame('renderedTemplate', $response->getContent());
    }

    /**
     * NEXT_MAJOR: Expect a NotFoundHttpException instead.
     *
     * @group legacy
     * @expectedDeprecation Trying to get a short object description for a non found object is deprecated since sonata-project/admin-bundle 3.76 and will be throw a 404 in version 4.0.
     */
    public function testGetShortObjectDescriptionActionEmptyObjectIdAsJson(): void
    {
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'uniqid' => 'asdasd123',
            '_format' => 'json',
        ]);

        $this->adminFetcher->method('get')->willReturn($this->admin);

        $this->admin->method('getObject')->with(null)->willReturn(null);
        $this->admin->method('id')->with(null)->willReturn('');
        $this->admin->method('toString')->with(null)->willReturn('');

        $response = ($this->action)($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame('{"result":{"id":"","label":""}}', $response->getContent());
    }

    public function testGetShortObjectDescriptionActionObjectAsJson(): void
    {
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'json',
        ]);
        $object = new \stdClass();

        $this->adminFetcher->method('get')->willReturn($this->admin);

        $this->admin->method('id')->with($object)->willReturn(42);
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('getTemplate')->with('short_object_description')->willReturn('template');
        $this->admin->method('toString')->with($object)->willReturn('bar');

        $response = ($this->action)($request);

        $this->assertSame('{"result":{"id":42,"label":"bar"}}', $response->getContent());
    }
}
