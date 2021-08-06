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

namespace Sonata\AdminBundle\Tests\Request\ParamConverter;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Request\AdminFetcher;
use Sonata\AdminBundle\Request\ParamConverter\AdminParamConverter;
use Sonata\AdminBundle\Tests\Fixtures\Admin\PostAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Admin\TagAdmin;
use Sonata\AdminBundle\Tests\Fixtures\Bundle\Entity\Post;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * NEXT_MAJOR: Remove this class.
 */
final class AdminParamConverterTest extends TestCase
{
    /**
     * @var Stub|AdminInterface
     */
    private $admin;

    /**
     * @var AdminParamConverter
     */
    private $converter;

    protected function setUp(): void
    {
        $this->admin = new PostAdmin('sonata.admin.post', Post::class, '');

        $container = new Container();
        $container->set('sonata.admin.post', $this->admin);

        $adminFetcher = new AdminFetcher(new Pool($container, ['sonata.admin.post']));

        $this->converter = new AdminParamConverter($adminFetcher);
    }

    public function testSupports(): void
    {
        $config = $this->createConfiguration(AbstractAdmin::class);
        $this->assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        $this->assertFalse($this->converter->supports($config));
    }

    public function testThrows404WhenAdminCodeNotFound(): void
    {
        $request = new Request();
        $request->attributes->set('_sonata_admin', 'not_existing_admin_code');

        $this->expectException(NotFoundHttpException::class);
        $this->converter->apply($request, $this->createConfiguration(PostAdmin::class));
    }

    public function testThrowsLogicExceptionWhenAdminClassDoesNotMatchTheConfiguredOne(): void
    {
        $request = new Request();
        $request->attributes->set('_sonata_admin', 'sonata.admin.post');

        $this->expectException(\LogicException::class);
        $this->converter->apply($request, $this->createConfiguration(TagAdmin::class));
    }

    public function testItSetsTheAdminInTheRequest(): void
    {
        $request = new Request();
        $request->attributes->set('_sonata_admin', 'sonata.admin.post');

        $variableName = 'myAdmin';
        $this->assertTrue($this->converter->apply($request, $this->createConfiguration(PostAdmin::class, $variableName)));
        $this->assertSame($this->admin, $request->attributes->get($variableName));
    }

    /**
     * @return Stub&ParamConverter
     * @phpstan-param class-string $class
     */
    private function createConfiguration(string $class, string $name = 'admin'): Stub
    {
        $config = $this->createStub(ParamConverter::class);

        $config
            ->method('getName')
            ->willReturn($name);

        $config
            ->method('getClass')
            ->willReturn($class);

        return $config;
    }
}
