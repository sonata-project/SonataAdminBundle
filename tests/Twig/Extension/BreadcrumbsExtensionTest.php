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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Tests\Fixtures\StubFilesystemLoader;
use Sonata\AdminBundle\Tests\Fixtures\StubTranslator;
use Sonata\AdminBundle\Twig\Extension\BreadcrumbsExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Twig\Environment;
use Twig\Extra\String\StringExtension;

final class BreadcrumbsExtensionTest extends TestCase
{
    /**
     * @var BreadcrumbsExtension
     */
    private $breadcrumbsExtension;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var Stub&BreadcrumbsBuilderInterface
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

        $this->breadcrumbBuilder = $this->createStub(BreadcrumbsBuilderInterface::class);

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

    private function removeExtraWhitespace(string $string): string
    {
        return trim(preg_replace('/\s+/', ' ', $string) ?? '');
    }
}
