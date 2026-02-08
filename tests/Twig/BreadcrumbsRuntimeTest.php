<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Twig;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\BreadcrumbsBuilderInterface;
use SensioLabs\AdminBundle\Tests\Fixtures\StubFilesystemLoader;
use SensioLabs\AdminBundle\Tests\Fixtures\StubTranslator;
use SensioLabs\AdminBundle\Twig\BreadcrumbsRuntime;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\UX\Icons\IconRendererInterface;
use Symfony\UX\Icons\Twig\UXIconExtension;
use Symfony\UX\Icons\Twig\UXIconRuntime;
use Twig\Environment;
use Twig\Extra\String\StringExtension;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

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
        $loader->addPath(__DIR__.'/../../src/Resources/views/', 'SensioLabsAdmin');

        $this->environment = new Environment($loader, [
            'strict_variables' => true,
            'cache' => false,
            'autoescape' => 'html',
            'optimizations' => 0,
        ]);
        $this->environment->addExtension(new TranslationExtension(new StubTranslator()));
        $this->environment->addExtension(new StringExtension());
        $this->environment->addExtension(new UXIconExtension());

        $iconRenderer = new class implements IconRendererInterface {
            public function renderIcon(string $name, array $attributes = []): string
            {
                $attrs = '';
                foreach ($attributes as $key => $value) {
                    if (\is_bool($value)) {
                        $attrs .= $value ? \sprintf(' %s', $key) : '';
                    } else {
                        $attrs .= \sprintf(' %s="%s"', $key, $value);
                    }
                }

                return \sprintf('<svg%s>%s</svg>', $attrs, $name);
            }
        };

        $this->environment->addRuntimeLoader(new FactoryRuntimeLoader([
            UXIconRuntime::class => static fn (): UXIconRuntime => new UXIconRuntime($iconRenderer),
        ]));

        $this->breadcrumbBuilder = static::createStub(BreadcrumbsBuilderInterface::class);

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
                static::createStub(AdminInterface::class),
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
            '<span class="admin-breadcrumb-item">Label for item 1</span>'
            .'<span class="admin-breadcrumb-separator"><svg class="w-4 h-4">lucide:chevron-right</svg></span>'
            .'<a href="https://sonata-project.org" class="admin-breadcrumb-item">'
                .' [trans domain=custom_translation_domain]Label for item 2 with custom_parameter[/trans] '
            .'</a>'
            .'<span class="admin-breadcrumb-separator"><svg class="w-4 h-4">lucide:chevron-right</svg></span>'
            .'<span class="admin-breadcrumb-current">Label for item 3</span>';

        static::assertSame(
            $expected,
            $this->removeExtraWhitespace($this->breadcrumbsRuntime->renderBreadcrumbs(
                $this->environment,
                static::createStub(AdminInterface::class),
                'not_important',
            ))
        );
    }

    private function removeExtraWhitespace(string $string): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/>\s+</', '><', $string) ?? '') ?? '');
    }
}
