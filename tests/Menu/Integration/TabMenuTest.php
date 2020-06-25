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

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Prophecy\Argument;
use Symfony\Contracts\Translation\TranslatorInterface;

class TabMenuTest extends BaseMenuTest
{
    protected $translator;

    public function getTranslator(): TranslatorInterface
    {
        if (isset($this->translator)) {
            return $this->translator;
        }

        return parent::getTranslator();
    }

    public function testLabelTranslationNominalCase(): void
    {
        $translatorProphecy = $this->prophesize(TranslatorInterface::class);
        $translatorProphecy
            ->trans(
                'some-label',
                [],
                Argument::any(), //messages or null
                null
            )
            ->willReturn('my-translation');

        $this->translator = $translatorProphecy->reveal();
        $factory = new MenuFactory();
        $menu = new MenuItem('test-menu', $factory);
        $menu->addChild('some-label', ['uri' => '/whatever']);
        $this->assertStringContainsString('my-translation', $this->renderMenu($menu));
    }

    public function testLabelTranslationWithParameters(): void
    {
        $params = ['my' => 'param'];
        $translatorProphecy = $this->prophesize(TranslatorInterface::class);
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
        $menu->addChild('some-label', ['uri' => '/whatever'])
            ->setExtra('translation_params', $params);

        $this->assertStringContainsString('my-translation', $this->renderMenu($menu));
    }

    public function testLabelTranslationDomainOverride(): void
    {
        $translatorProphecy = $this->prophesize(TranslatorInterface::class);
        $translatorProphecy
            ->trans('some-label', [], 'my_local_domain', null)
            ->willReturn('my-translation');
        $translatorProphecy
            ->trans('some-other-label', [], 'my_global_domain', null)
            ->willReturn('my-other-translation');

        $this->translator = $translatorProphecy->reveal();
        $factory = new MenuFactory();
        $menu = new MenuItem('test-menu', $factory);
        $menu->setExtra('translation_domain', 'my_global_domain');
        $menu->addChild('some-label', ['uri' => '/whatever'])
            ->setExtra('translation_domain', 'my_local_domain');
        $menu->addChild('some-other-label', ['uri' => '/whatever']);

        $html = $this->renderMenu($menu);
        $this->assertStringContainsString('my-translation', $html);
        $this->assertStringContainsString('my-other-translation', $html);
    }

    protected function getTemplate()
    {
        return 'Core/tab_menu_template.html.twig';
    }
}
