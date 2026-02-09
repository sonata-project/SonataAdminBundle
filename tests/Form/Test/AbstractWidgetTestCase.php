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

namespace SensioLabs\AdminBundle\Tests\Form\Test;

use SensioLabs\AdminBundle\Tests\Fixtures\StubTranslator;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

/**
 * Base class for tests checking rendering of form widgets.
 */
abstract class AbstractWidgetTestCase extends TypeTestCase
{
    private FormRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $environment = $this->getEnvironment();

        $this->renderer = new FormRenderer(
            $this->getRenderingEngine($environment),
            $this->createMock(CsrfTokenManagerInterface::class)
        );

        $environment->addRuntimeLoader(new FactoryRuntimeLoader([
            FormRenderer::class => fn (): FormRenderer => $this->renderer,
        ]));
        $environment->addExtension(new FormExtension());
    }

    final public function getRenderer(): FormRenderer
    {
        return $this->renderer;
    }

    protected function getEnvironment(): Environment
    {
        $loader = new FilesystemLoader($this->getTemplatePaths());

        $environment = new Environment($loader, [
            'strict_variables' => true,
        ]);
        $environment->addExtension(new TranslationExtension(new StubTranslator()));

        return $environment;
    }

    /**
     * Returns a list of template paths.
     *
     * @return string[]
     */
    protected function getTemplatePaths(): array
    {
        $twigPaths = array_filter([
            __DIR__.'/../../../vendor/symfony/twig-bridge/Resources/views/Form',
            __DIR__.'/../../../vendor/symfony/symfony/src/Symfony/Bridge/Twig/Resources/views/Form',
        ], is_dir(...));

        return $twigPaths;
    }

    protected function getRenderingEngine(Environment $environment): TwigRendererEngine
    {
        return new TwigRendererEngine(['form_div_layout.html.twig'], $environment);
    }

    /**
     * Renders widget from FormView, in SensioLabsAdmin context, with optional view variables $vars. Returns plain HTML.
     *
     * @param array<string, mixed> $vars
     */
    final protected function renderWidget(FormView $view, array $vars = []): string
    {
        return $this->renderer->searchAndRenderBlock($view, 'widget', $vars);
    }

    /**
     * Helper method to strip newline and space characters from html string to make comparing easier.
     */
    final protected function cleanHtmlWhitespace(string $html): string
    {
        return preg_replace_callback(
            '/\s*>([^<]+)</',
            static fn (array $value): string => '>'.trim($value[1]).'<',
            $html
        ) ?? '';
    }

    final protected function cleanHtmlAttributeWhitespace(string $html): string
    {
        return preg_replace_callback(
            '~<([A-Z0-9]+) \K(.*?)>~i',
            static fn (array $m): string => preg_replace('~\s*~', '', $m[0]) ?? '',
            $html
        ) ?? '';
    }
}
