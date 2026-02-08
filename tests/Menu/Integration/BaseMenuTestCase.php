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

namespace SensioLabs\AdminBundle\Tests\Menu\Integration;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Renderer\TwigRenderer;
use Knp\Menu\Twig\MenuExtension;
use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Tests\Fixtures\StubTranslator;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Icons\IconRendererInterface;
use Symfony\UX\Icons\Twig\UXIconExtension;
use Symfony\UX\Icons\Twig\UXIconRuntime;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * Base class for tests checking rendering of twig templates.
 */
abstract class BaseMenuTestCase extends TestCase
{
    private Environment $environment;

    protected function setUp(): void
    {
        // Adapt to both bundle and project-wide test strategy
        $twigPaths = array_filter([
            \sprintf('%s/../../../../../../vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views', __DIR__),
            \sprintf('%s/../../../vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views', __DIR__),
            \sprintf('%s/../../../src/Resources/views', __DIR__),
        ], is_dir(...));

        $loader = new FilesystemLoader($twigPaths);
        $this->environment = new Environment($loader, ['strict_variables' => true]);
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
    }

    abstract protected function getTemplate(): string;

    protected function getTranslator(): TranslatorInterface
    {
        return new StubTranslator();
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function renderMenu(ItemInterface $item, array $options = []): string
    {
        $this->environment->addExtension(new TranslationExtension($this->getTranslator()));
        $this->environment->addExtension(new MenuExtension());

        $renderer = new TwigRenderer(
            $this->environment,
            $this->getTemplate(),
            $this->createMock(MatcherInterface::class)
        );

        return $renderer->render($item, $options);
    }

    /**
     * Helper method to strip newline and space characters from html string to make comparing easier.
     */
    protected function cleanHtmlWhitespace(string $html): string
    {
        $html = preg_replace_callback('/>([^<]+)</', static fn ($value): string => \sprintf('>%s<', trim($value[1])), $html);

        return $html ?? '';
    }
}
