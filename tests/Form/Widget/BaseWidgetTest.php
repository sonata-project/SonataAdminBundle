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

namespace Sonata\AdminBundle\Tests\Form\Widget;

use Sonata\AdminBundle\Tests\Fixtures\StubTranslator;
use Sonata\Form\Test\AbstractWidgetTestCase;
use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Base class for tests checking rendering of form widgets with form_admin_fields.html.twig and
 * filter_admin_fields.html.twig. Template to use is defined by $this->type variable, that needs to be overridden in
 * child classes.
 */
abstract class BaseWidgetTest extends AbstractWidgetTestCase
{
    /**
     * Current template type, form or filter.
     *
     * @var string
     */
    protected $type;

    /**
     * @var array<string, mixed>
     */
    protected $sonataAdmin = [
        'name' => null,
        'admin' => null,
        'value' => null,
        'edit' => 'standard',
        'inline' => 'natural',
        'field_description' => null,
        'block_name' => false,
        'options' => [
            'form_type' => 'vertical',
            'use_icheck' => true,
        ],
    ];

    protected function getEnvironment(): Environment
    {
        $environment = parent::getEnvironment();
        $environment->addGlobal('sonata_admin', $this->getSonataAdmin());
        $environment->addExtension(new RoutingExtension($this->createStub(UrlGeneratorInterface::class)));
        $environment->addExtension(new HttpKernelExtension());
        if (!$environment->hasExtension(TranslationExtension::class)) {
            $environment->addExtension(new TranslationExtension(new StubTranslator()));
        }

        return $environment;
    }

    protected function getRenderingEngine(Environment $environment): TwigRendererEngine
    {
        if (!\in_array($this->type, ['form', 'filter'], true)) {
            throw new \Exception(
                'Please override $this->type in your test class specifying template to use (either form or filter)'
            );
        }

        return new TwigRendererEngine(
            [\sprintf('%s_admin_fields.html.twig', $this->type)],
            $environment
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getSonataAdmin(): array
    {
        return $this->sonataAdmin;
    }

    protected function getTemplatePaths(): array
    {
        $twigPaths = array_filter([
            \sprintf('%s/../../../vendor/symfony/symfony/src/Symfony/Bridge/Twig/Resources/views/Form', __DIR__),
            \sprintf('%s/../../../src/Resources/views/Form', __DIR__),
        ], 'is_dir');

        return array_merge(parent::getTemplatePaths(), $twigPaths);
    }
}
