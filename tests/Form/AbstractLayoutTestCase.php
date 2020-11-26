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

namespace Sonata\AdminBundle\Tests\Form;

use Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;
use Sonata\Form\Fixtures\StubTranslator;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

abstract class AbstractLayoutTestCase extends FormIntegrationTestCase
{
    /**
     * @var FormRenderer
     */
    protected $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionClass(TwigRendererEngine::class);
        $bridgeDirectory = \dirname($reflection->getFileName()).'/../Resources/views/Form';

        $loader = new FilesystemLoader([
            __DIR__.'/../../src/Resources/views/Form',
            $bridgeDirectory,
        ]);

        $environment = new Environment($loader, ['strict_variables' => true]);
        $environment->addExtension(new TranslationExtension(new StubTranslator()));
        $environment->addExtension(new FormExtension());

        $rendererEngine = new TwigRendererEngine([
            'form_admin_fields.html.twig',
        ], $environment);

        $csrfTokenManager = $this->createStub(CsrfTokenManagerInterface::class);

        $environment->addRuntimeLoader(new FactoryRuntimeLoader([
            FormRenderer::class => static function () use ($rendererEngine, $csrfTokenManager): FormRendererInterface {
                return new FormRenderer($rendererEngine, $csrfTokenManager);
            },
        ]));

        $this->renderer = $environment->getRuntime(FormRenderer::class);
    }

    /**
     * @see https://github.com/symfony/symfony/blob/e68da40f5649bb0266c74c2e1e4bbf83f9c6bb13/src/Symfony/Component/Form/Tests/AbstractLayoutTest.php#L64
     */
    final protected function assertMatchesXpath(string $html, string $expression, int $count = 1): void
    {
        $dom = new \DOMDocument('UTF-8');
        try {
            // Wrap in <root> node so we can load HTML with multiple tags at
            // the top level
            $dom->loadXML('<root>'.$html.'</root>');
        } catch (\Exception $e) {
            $this->fail(sprintf(
                "Failed loading HTML:\n\n%s\n\nError: %s",
                $html,
                $e->getMessage()
            ));
        }
        $xpath = new \DOMXPath($dom);
        $nodeList = $xpath->evaluate('/root'.$expression);

        if ($nodeList->length !== $count) {
            $dom->formatOutput = true;
            $this->fail(sprintf(
                "Failed asserting that \n\n%s\n\nmatches exactly %s. Matches %s in \n\n%s",
                $expression,
                1 === $count ? 'once' : $count.' times',
                1 === $nodeList->length ? 'once' : $nodeList->length.' times',
                // strip away <root> and </root>
                substr($dom->saveHTML(), 6, -8)
            ));
        } else {
            $this->addToAssertionCount(1);
        }
    }

    protected function getTypeExtensions(): array
    {
        return [
            new FormTypeFieldExtension([], [
                'form_type' => 'horizontal',
            ]),
        ];
    }

    protected function renderRow(FormView $view, array $vars = []): string
    {
        return (string) $this->renderer->searchAndRenderBlock($view, 'row', $vars);
    }

    protected function renderErrors(FormView $view): string
    {
        return (string) $this->renderer->searchAndRenderBlock($view, 'errors');
    }
}
