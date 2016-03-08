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

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Prophecy\Argument;

class TabMenuTest extends BaseMenuTest
{
    protected $translator;

    protected function getTemplate()
    {
        return 'Core/tab_menu_template.html.twig';
    }

    public function getTranslator()
    {
        if (isset($this->translator)) {
            return $this->translator;
        }

        return parent::getTranslator();
    }

    public function testLabelTranslationNominalCase()
    {
        $translatorProphecy = $this->prophesize(
            'Symfony\Component\Translation\TranslatorInterface'
        );
        $translatorProphecy
            ->trans(
                'some-label',
                array(),
                Argument::any(), //messages or null
                null
            )
            ->willReturn('my-translation');

        $this->translator = $translatorProphecy->reveal();
        $factory = new MenuFactory();
        $menu = new MenuItem('test-menu', $factory);
        $menu->addChild('some-label', array('uri' => '/whatever'));
        $this->assertContains('my-translation', $this->renderMenu($menu));
    }

    public function testLabelTranslationWithParameters()
    {
        $params = array('my' => 'param');
        $translatorProphecy = $this->prophesize(
            'Symfony\Component\Translation\TranslatorInterface'
        );
        $translatorProphecy
            ->trans(
                'some-label',
                $params,
                Argument::any(), // messages or null
                null
            )
            ->willReturn('my-translation');

        $this->translator = $translatorProphecy->reveal();
        $factory = new MenuFactory();
        $menu = new MenuItem('test-menu', $factory);
        $menu->addChild('some-label', array('uri' => '/whatever'))
            ->setExtra('translation_params', $params);

        $this->assertContains('my-translation', $this->renderMenu($menu));
    }

    public function testLabelTranslationDomainOverride()
    {
        $translatorProphecy = $this->prophesize(
            'Symfony\Component\Translation\TranslatorInterface'
        );
        $translatorProphecy
            ->trans('some-label', array(), 'my_local_domain', null)
            ->willReturn('my-translation');
        $translatorProphecy
            ->trans('some-other-label', array(), 'my_global_domain', null)
            ->willReturn('my-other-translation');

        $this->translator = $translatorProphecy->reveal();
        $factory = new MenuFactory();
        $menu = new MenuItem('test-menu', $factory);
        $menu->setExtra('translation_domain', 'my_global_domain');
        $menu->addChild('some-label', array('uri' => '/whatever'))
            ->setExtra('translation_domain', 'my_local_domain');
        $menu->addChild('some-other-label', array('uri' => '/whatever'));

        $html = $this->renderMenu($menu);
        $this->assertContains('my-translation', $html);
        $this->assertContains('my-other-translation', $html);
    }
}
