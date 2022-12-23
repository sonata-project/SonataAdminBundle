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
use Sonata\AdminBundle\Action\GetShortObjectDescriptionAction;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\BadRequestParamHttpException;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Sonata\AdminBundle\Templating\MutableTemplateRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class GetShortObjectDescriptionActionTest extends TestCase
{
    /**
     * @var Stub&AdminFetcherInterface
     */
    private AdminFetcherInterface $adminFetcher;

    private Environment $twig;

    private GetShortObjectDescriptionAction $action;

    /**
     * @var AdminInterface<object>&MockObject
     */
    private AdminInterface $admin;

    protected function setUp(): void
    {
        $this->twig = new Environment(new ArrayLoader(['short_object_description' => 'renderedTemplate']));
        $this->admin = $this->createMock(AdminInterface::class);
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

        $this->admin->expects(static::never())->method('setRequest');

        $this->expectException(NotFoundHttpException::class);

        ($this->action)($request);
    }

    public function testGetShortObjectDescriptionActionObjectDoesNotExist(): void
    {
        $this->adminFetcher->method('get')->willReturn($this->admin);

        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
        ]);

        $this->admin->method('getObject')->with(42)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        ($this->action)($request);
    }

    public function testGetShortObjectDescriptionActionEmptyObjectId(): void
    {
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);

        $this->adminFetcher->method('get')->willReturn($this->admin);

        $this->admin->method('getObject')->with(null)->willReturn(null);

        $this->expectException(BadRequestParamHttpException::class);
        ($this->action)($request);
    }

    public function testGetShortObjectDescriptionActionObject(): void
    {
        $templateRegistry = new MutableTemplateRegistry([
            'short_object_description' => 'short_object_description',
        ]);

        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            '_format' => 'html',
        ]);
        $object = new \stdClass();

        $this->adminFetcher->method('get')->willReturn($this->admin);

        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('toString')->with($object)->willReturn('bar');
        $this->admin->method('getCode')->willReturn('sonata.post.admin');
        $this->admin->method('getTemplateRegistry')->willReturn($templateRegistry);

        $response = ($this->action)($request);

        static::assertSame('renderedTemplate', $response->getContent());
    }

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

        $this->expectException(BadRequestParamHttpException::class);
        ($this->action)($request);
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

        $this->admin->method('id')->with($object)->willReturn('42');
        $this->admin->method('getObject')->with(42)->willReturn($object);
        $this->admin->method('toString')->with($object)->willReturn('bar');

        $response = ($this->action)($request);

        static::assertSame('{"result":{"id":"42","label":"bar"}}', $response->getContent());
    }

    public function testGetShortObjectDescriptionActionSubclassQueryParameterTemporaryRemoved(): void
    {
        $request = new Request([
            '_sonata_admin' => 'sonata.post.admin',
            'objectId' => 42,
            'uniqid' => 'asdasd123',
            'subclass' => $subclass = uniqid('subclass'),
            '_format' => 'json',
        ]);
        $object = new \stdClass();

        $this->adminFetcher->method('get')->willReturn($this->admin);

        $this->admin->method('id')->with($object)->willReturn('42');
        $this->admin->method('getObject')->with(42)->willReturnCallback(static function () use ($object, $request) {
            static::assertFalse($request->query->has('subclass'), 'subclass query parameter should be removed at this stage');

            return $object;
        });

        $this->admin->method('toString')->with($object)->willReturn('bar');

        ($this->action)($request);

        static::assertSame(
            $subclass,
            $request->query->get('subclass'),
            'subclass query parameter should be restored at this stage'
        );
    }
}
