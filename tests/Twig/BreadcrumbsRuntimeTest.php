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

namespace Sonata\AdminBundle\Tests\Twig;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use Sonata\AdminBundle\Tests\Fixtures\StubFilesystemLoader;
use Sonata\AdminBundle\Tests\Fixtures\StubTranslator;
use Sonata\AdminBundle\Twig\BreadcrumbsRuntime;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Twig\Environment;
use Twig\Extra\String\StringExtension;

final class BreadcrumbsRuntimeTest extends TestCase
{
    private BreadcrumbsRuntime $breadcrumbsRuntime;

    private Environment $environment;

    /**
     * @var Stub&BreadcrumbsBuilderInterface
     */
    private BreadcrumbsBuilderInterface $breadcrumbBuilder;

    protected function setUp(): void
    {
        $loader = new StubFilesystemLoader();
        $loader->addPath(__DIR__.'/../../src/Resources/views/', 'SonataAdmin');

        $this->environment = new Environment($loader, [
            'strict_variables' => true,
            'cache' => false,
            'autoescape' => 'html',
            'optimizations' => 0,
        ]);
        $this->environment->addExtension(new TranslationExtension(new StubTranslator()));
        $this->environment->addExtension(new StringExtension());

        $this->breadcrumbBuilder = $this->createStub(BreadcrumbsBuilderInterface::class);

        $this->breadcrumbsRuntime = new BreadcrumbsRuntime($this->breadcrumbBuilder);
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
            ->willReturnMap([
                ['translation_domain', 'messages', false],
                ['translation_params', [], []],
            ]);

        $item3 = $this->createMock(ItemInterface::class);
        $item3
            ->method('getLabel')
            ->willReturn('Label for item 3 with %parameter%');
        $item3
            ->method('getExtra')
            ->willReturnMap([
                ['translation_domain', 'messages', 'custom_translation_domain'],
                ['translation_params', [], ['%parameter%' => 'custom_parameter']],
            ]);

        $this->breadcrumbBuilder
            ->method('getBreadcrumbs')
            ->willReturn([$item, $item2, $item3]);

        static::assertSame(
            'Label for item 2 &gt; [trans domain=custom_translation_domain]Label for item 3 with custom_parameter[/trans]',
            $this->removeExtraWhitespace($this->breadcrumbsRuntime->renderBreadcrumbsForTitle(
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
            ->willReturnMap([
                ['translation_domain', 'messages', false],
                ['translation_params', [], []],
            ]);

        $item2 = $this->createMock(ItemInterface::class);
        $item2
            ->method('getLabel')
            ->willReturn('Label for item 2 with %parameter%');
        $item2
            ->method('getUri')
            ->willReturn('https://sonata-project.org');
        $item2
            ->method('getExtra')
            ->willReturnMap([
                ['translation_domain', 'messages', 'custom_translation_domain'],
                ['translation_params', [], ['%parameter%' => 'custom_parameter']],
            ]);

        $item3 = $this->createMock(ItemInterface::class);
        $item3
            ->method('getLabel')
            ->willReturn('Label for item 3');
        $item3
            ->method('getExtra')
            ->willReturnMap([
                ['translation_domain', 'messages', false],
                ['translation_params', [], []],
            ]);

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

        static::assertSame(
            $expected,
            $this->removeExtraWhitespace($this->breadcrumbsRuntime->renderBreadcrumbs(
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
