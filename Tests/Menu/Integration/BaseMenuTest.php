<?php

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
use Knp\Menu\Renderer\TwigRenderer;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Tests\Extension\Fixtures\StubFilesystemLoader;
use Symfony\Bundle\FrameworkBundle\Tests\Templating\Helper\Fixtures\StubTranslator;

/**
 * Class BaseTemplateTest.
 *
 * Base class for tests checking rendering of twig templates
 */
abstract class BaseMenuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $twigPaths = array(
            __DIR__.'/../../../vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views',
            __DIR__.'/../../../Resources/views',
        );
        $loader = new StubFilesystemLoader($twigPaths);
        $this->environment = new \Twig_Environment($loader, array('strict_variables' => true));
    }

    abstract protected function getTemplate();

    protected function getTranslator()
    {
        return new StubTranslator();
    }

    protected function renderMenu(ItemInterface $item, array $options = array())
    {
        $this->environment->addExtension(new TranslationExtension($this->getTranslator()));
        $this->renderer = new TwigRenderer(
            $this->environment,
            $this->getTemplate(),
            $this->getMock('Knp\Menu\Matcher\MatcherInterface')
        );

        return $this->renderer->render($item, $options);
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
