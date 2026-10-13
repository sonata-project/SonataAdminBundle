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

namespace Sonata\AdminBundle\Tests\Templating;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Templating\MutableTemplateRegistry;
use Sonata\AdminBundle\Templating\MutableTemplateRegistryInterface;

final class MutableTemplateRegistryTest extends TestCase
{
    private MutableTemplateRegistryInterface $templateRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->templateRegistry = new MutableTemplateRegistry(
            ['list' => '@FooAdmin/CRUD/list.html.twig'],
            [
                'default' => ['list' => '@FooAdmin/CRUD/list.html.twig'],
                'second_theme' => ['list' => '@FooAdmin/CRUD_v2/list.html.twig'],
            ]
        );
    }

    public function testGetTemplates(): void
    {
        static::assertSame(['list' => '@FooAdmin/CRUD/list.html.twig'], $this->templateRegistry->getTemplates());

        $templates = [
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates);
        static::assertSame($templates + ['list' => '@FooAdmin/CRUD/list.html.twig'], $this->templateRegistry->getTemplates());
    }

    public function testGetThemedTemplates(): void
    {
        $templates = [
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        static::assertSame(['list' => '@FooAdmin/CRUD_v2/list.html.twig'], $this->templateRegistry->getTemplates('second_theme'));
        $this->templateRegistry->setTemplates($templates, 'second_theme');
        static::assertSame($templates + ['list' => '@FooAdmin/CRUD_v2/list.html.twig'], $this->templateRegistry->getTemplates('second_theme'));
    }

    public function testGetTemplateAfterSetTemplate(): void
    {
        $this->templateRegistry->setTemplate('edit', '@FooAdmin/CRUD/edit.html.twig');

        static::assertTrue($this->templateRegistry->hasTemplate('edit'));
        static::assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));

        static::assertFalse($this->templateRegistry->hasTemplate('nonexist_template'));
    }

    public function testGetTemplateAfterSetTemplates(): void
    {
        $templates = [
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates);

        static::assertTrue($this->templateRegistry->hasTemplate('edit'));
        static::assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));

        static::assertFalse($this->templateRegistry->hasTemplate('nonexist_template'));
    }

    public function testGetThemedTemplateAfterSetTemplates(): void
    {
        $templates = [
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates, 'second_theme');

        static::assertTrue($this->templateRegistry->hasTemplate('edit', 'second_theme'));
        static::assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit', 'second_theme'));

        static::assertFalse($this->templateRegistry->hasTemplate('nonexist_template', 'second_theme'));
    }

    public function testSetThemedTemplatesOnNewThem(): void
    {
        $templates = [
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates, 'empty_theme');

        static::assertTrue($this->templateRegistry->hasTemplate('edit', 'empty_theme'));
        static::assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit', 'empty_theme'));

        static::assertFalse($this->templateRegistry->hasTemplate('nonexist_template', 'empty_theme'));
    }
}
