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

namespace Sonata\AdminBundle\Tests\Twig\Extension;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Tests\Fixtures\StubFilesystemLoader;
use Sonata\AdminBundle\Tests\Fixtures\StubTranslator;
use Sonata\AdminBundle\Twig\Extension\BreadcrumbsExtension;
// NEXT_MAJOR: Remove next line.
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Twig\Environment;
use Twig\Extra\String\StringExtension;

final class BreadcrumbsExtensionTest extends TestCase
{
    // NEXT_MAJOR: Remove next line.
    use ExpectDeprecationTrait;

    /**
     * @var BreadcrumbsExtension
     */
    private $breadcrumbsExtension;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * NEXT_MAJOR: Replace to MockObject by Stub.
     *
     * @var MockObject&BreadcrumbsBuilderInterface
     */
    private $breadcrumbBuilder;

    protected function setUp(): void
    {
        $loader = new StubFilesystemLoader();
        $loader->addPath(__DIR__.'/../../../src/Resources/views/', 'SonataAdmin');

        $this->environment = new Environment($loader, [
            'strict_variables' => true,
            'cache' => false,
            'autoescape' => 'html',
            'optimizations' => 0,
        ]);
        $this->environment->addExtension(new TranslationExtension(new StubTranslator()));
        $this->environment->addExtension(new StringExtension());

        // NEXT_MAJOR: Use $this->createStub instead.
        $this->breadcrumbBuilder = $this->createMock(BreadcrumbsBuilderInterface::class);

        $this->breadcrumbsExtension = new BreadcrumbsExtension($this->breadcrumbBuilder);
    }

    public function testBreadcrumbsForTitle(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item2 = $this->createMock(ItemInterface::class);
        $item2
            ->method('getLabel')
            ->willReturn('Label for item 2');
        $item2
            ->method('getExtra')
            ->withConsecutive(
                ['translation_domain'],
                ['translation_params']
            )
            ->willReturnOnConsecutiveCalls(
                false,
                []
            );

        $item3 = $this->createMock(ItemInterface::class);
        $item3
            ->method('getLabel')
            ->willReturn('Label for item 3 with %parameter%');
        $item3
            ->method('getExtra')
            ->withConsecutive(
                ['translation_domain'],
                ['translation_params']
            )
            ->willReturnOnConsecutiveCalls(
                'custom_translation_domain',
                ['%parameter%' => 'custom_parameter']
            );

        $this->breadcrumbBuilder
            ->method('getBreadcrumbs')
            ->willReturn([$item, $item2, $item3]);

        $this->assertSame(
            'Label for item 2 &gt; [trans domain=custom_translation_domain]Label for item 3 with custom_parameter[/trans]',
            $this->removeExtraWhitespace($this->breadcrumbsExtension->renderBreadcrumbsForTitle(
                $this->environment,
                $this->createStub(AdminInterface::class),
                'not_important',
            ))
        );
    }

    public function testBreadcrumbs(): void
    {
        $item = $this->createMock(ItemInterface::class);
        $item
            ->method('getLabel')
            ->willReturn('Label for item 1');
        $item
            ->method('getExtra')
            ->withConsecutive(
                ['translation_domain'],
                ['translation_params']
            )
            ->willReturnOnConsecutiveCalls(
                false,
                []
            );

        $item2 = $this->createMock(ItemInterface::class);
        $item2
            ->method('getLabel')
            ->willReturn('Label for item 2 with %parameter%');
        $item2
            ->method('getUri')
            ->willReturn('https://sonata-project.org');
        $item2
            ->method('getExtra')
            ->withConsecutive(
                ['translation_domain'],
                ['translation_params']
            )
            ->willReturnOnConsecutiveCalls(
                'custom_translation_domain',
                ['%parameter%' => 'custom_parameter']
            );

        $item3 = $this->createMock(ItemInterface::class);
        $item3
            ->method('getLabel')
            ->willReturn('Label for item 3');
        $item3
            ->method('getExtra')
            ->withConsecutive(
                ['translation_domain'],
                ['translation_params']
            )
            ->willReturnOnConsecutiveCalls(
                false,
                []
            );

        $this->breadcrumbBuilder
            ->method('getBreadcrumbs')
            ->willReturn([$item, $item2, $item3]);

        $expected =
            '<li><span>Label for item 1</span></li>'
            .'<li>'
                .'<a href="https://sonata-project.org"> '
                    .'[trans domain=custom_translation_domain]Label for item 2 with custom_parameter[/trans] '
                .'</a>'
            .'</li>'
            .'<li class="active"><span>Label for item 3</span></li>';

        $this->assertSame(
            $expected,
            $this->removeExtraWhitespace($this->breadcrumbsExtension->renderBreadcrumbs(
                $this->environment,
                $this->createStub(AdminInterface::class),
                'not_important',
            ))
        );
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testUsingExternalBreadcrumbsBuilderForBC(): void
    {
        $customBreadcrumbsBuilder = new class() implements BreadcrumbsBuilderInterface {
            /**
             * @var int
             */
            public $numberOfCalls = 0;

            public function getBreadcrumbs(AdminInterface $admin, $action): array
            {
                ++$this->numberOfCalls;

                return [];
            }

            public function buildBreadcrumbs(AdminInterface $admin, $action, ?ItemInterface $menu = null): void
            {
            }
        };

        $this->breadcrumbBuilder
            ->expects($this->never())
            ->method('getBreadcrumbs');

        $this->expectDeprecation('Overriding "breadcrumbs_builder" parameter in twig templates is deprecated since sonata-project/admin-bundle version 3.x and this parameter will be removed in 4.0. Use "sonata.admin.breadcrumbs_builder" service instead.');

        $this->breadcrumbsExtension->renderBreadcrumbs(
            $this->environment,
            $this->createStub(AdminInterface::class),
            'not_important',
            $customBreadcrumbsBuilder
        );

        $this->breadcrumbsExtension->renderBreadcrumbsForTitle(
            $this->environment,
            $this->createStub(AdminInterface::class),
            'not_important',
            $customBreadcrumbsBuilder
        );

        $this->assertSame(2, $customBreadcrumbsBuilder->numberOfCalls);
    }

    private function removeExtraWhitespace(string $string): string
    {
        return trim(preg_replace(
            '/\s+/',
            ' ',
            $string
        ));
    }
}
