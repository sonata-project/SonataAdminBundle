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
use Symfony\Contracts\Translation\TranslatorInterface;

final class TabMenuTest extends BaseMenuTest
{
    /**
     * @var TranslatorInterface|null
     */
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
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')
            ->with(
                'some-label',
                [],
                null, //messages or null
                null
            )
            ->willReturn('my-translation');

        $factory = new MenuFactory();
        $menu = new MenuItem('test-menu', $factory);
        $menu->addChild('some-label', ['uri' => '/whatever']);
        self::assertStringContainsString('my-translation', $this->renderMenu($menu));
    }

    public function testLabelTranslationWithParameters(): void
    {
        $params = ['my' => 'param'];
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')
            ->with(
                'some-label',
                $params,
                null, // messages or null
                null
            )
            ->willReturn('my-translation');

        $factory = new MenuFactory();
        $menu = new MenuItem('test-menu', $factory);
        $menu->addChild('some-label', ['uri' => '/whatever'])
            ->setExtra('translation_params', $params);

        self::assertStringContainsString('my-translation', $this->renderMenu($menu));
    }

    public function testLabelTranslationDomainOverride(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnMap([
            ['some-label', [], 'my_local_domain', null, 'my-translation'],
            ['some-other-label', [], 'my_global_domain', null, 'my-other-translation'],
        ]);

        $factory = new MenuFactory();
        $menu = new MenuItem('test-menu', $factory);
        $menu->setExtra('translation_domain', 'my_global_domain');
        $menu->addChild('some-label', ['uri' => '/whatever'])
            ->setExtra('translation_domain', 'my_local_domain');
        $menu->addChild('some-other-label', ['uri' => '/whatever']);

        $html = $this->renderMenu($menu);
        self::assertStringContainsString('my-translation', $html);
        self::assertStringContainsString('my-other-translation', $html);
    }

    protected function getTemplate(): string
    {
        return 'Core/tab_menu_template.html.twig';
    }
}
