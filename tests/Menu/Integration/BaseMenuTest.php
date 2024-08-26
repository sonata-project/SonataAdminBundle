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

namespace Sonata\AdminBundle\Tests\Menu\Integration;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Renderer\TwigRenderer;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Tests\Fixtures\StubTranslator;
use Sonata\AdminBundle\Twig\Extension\IconExtension;
use Sonata\AdminBundle\Twig\IconRuntime;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Base class for tests checking rendering of twig templates.
 */
abstract class BaseMenuTest extends TestCase
{
    private Environment $environment;

    protected function setUp(): void
    {
        // Adapt to both bundle and project-wide test strategy
        $twigPaths = array_filter([
            \sprintf('%s/../../../../../../vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views', __DIR__),
            \sprintf('%s/../../../vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views', __DIR__),
            \sprintf('%s/../../../src/Resources/views', __DIR__),
        ], 'is_dir');

        $loader = new FilesystemLoader($twigPaths);
        $this->environment = new Environment($loader, ['strict_variables' => true]);
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
        $this->environment->addExtension(new IconExtension(new IconRuntime()));
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
