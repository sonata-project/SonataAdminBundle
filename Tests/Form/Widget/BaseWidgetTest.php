<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Form\Widget;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Tests\Extension\Fixtures\StubFilesystemLoader;
use Symfony\Bundle\FrameworkBundle\Tests\Templating\Helper\Fixtures\StubTranslator;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class BaseWidgetTest.
 *
 * Base class for tests checking rendering of form widgets with form_admin_fields.html.twig and
 * filter_admin_fields.html.twig. Template to use is defined by $this->type variable, that needs to be overridden in
 * child classes.
 */
abstract class BaseWidgetTest extends TypeTestCase
{
    /**
     * @var FormExtension
     */
    protected $extension;

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * Current template type, form or filter.
     *
     * @var string
     */
    protected $type = null;

    /**
     * @var array
     */
    protected $sonataAdmin = array(
        'name'              => null,
        'admin'             => null,
        'value'             => null,
        'edit'              => 'standard',
        'inline'            => 'natural',
        'field_description' => null,
        'block_name'        => false,
        'options'           => array(
            'form_type'  => 'vertical',
            'use_icheck' => true,
        ),
    );

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        if (!in_array($this->type, array('form', 'filter'))) {
            throw new \Exception('Please override $this->type in your test class specifying template to use (either form or filter)');
        }

        $rendererEngine = new TwigRendererEngine(array(
            $this->type.'_admin_fields.html.twig',
        ));

        $csrfManagerClass =
            interface_exists('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface') ?
            'Symfony\Component\Security\Csrf\CsrfTokenManagerInterface' :
            'Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface';

        $renderer = new TwigRenderer($rendererEngine, $this->getMock($csrfManagerClass));

        $this->extension = new FormExtension($renderer);

        //this is ugly workaround for different build strategies and, possibly,
        //different TwigBridge installation directories
        $twigPaths = array_filter(array(
            __DIR__.'/../../../vendor/symfony/twig-bridge/Symfony/Bridge/Twig/Resources/views/Form',
            __DIR__.'/../../../vendor/symfony/twig-bridge/Resources/views/Form',
            __DIR__.'/../../../vendor/symfony/symfony/src/Symfony/Bridge/Twig/Resources/views/Form',
            __DIR__.'/../../../../../symfony/symfony/src/Symfony/Bridge/Twig/Resources/views/Form',
        ), 'is_dir');

        $twigPaths[] = __DIR__.'/../../../Resources/views/Form';

        $loader = new StubFilesystemLoader($twigPaths);

        $this->environment = new \Twig_Environment($loader, array('strict_variables' => true));
        $this->environment->addGlobal('sonata_admin', $this->getSonataAdmin());
        $this->environment->addExtension(new TranslationExtension(new StubTranslator()));

        $this->environment->addExtension($this->extension);

        $this->extension->initRuntime($this->environment);
    }

    protected function getSonataAdmin()
    {
        return $this->sonataAdmin;
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->extension = null;
    }

    /**
     * Renders widget from FormView, in SonataAdmin context, with optional view variables $vars. Returns plain HTML.
     *
     * @param FormView $view
     * @param array    $vars
     *
     * @return string
     */
    protected function renderWidget(FormView $view, array $vars = array())
    {
        return (string) $this->extension->renderer->searchAndRenderBlock($view, 'widget', $vars);
    }

    /**
     * Helper method to strip newline and space characters from html string to make comparing easier.
     *
     * @param string $html
     *
     * @return string
     */
    protected function cleanHtmlWhitespace($html)
    {
        $html = preg_replace_callback('/>([^<]+)</', function ($value) {
            return '>'.trim($value[1]).'<';
        }, $html);

        return $html;
    }
}
