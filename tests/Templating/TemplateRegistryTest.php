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
use Sonata\AdminBundle\Templating\TemplateRegistry;

final class TemplateRegistryTest extends TestCase
{
    private TemplateRegistry $templateRegistry;

    protected function setUp(): void
    {
        $this->templateRegistry = new TemplateRegistry(
            [
                'list' => '@FooAdmin/CRUD/list.html.twig',
                'show' => '@FooAdmin/CRUD/show.html.twig',
                'edit' => '@FooAdmin/CRUD/edit.html.twig',
            ],
            [
                'default' => [
                    'list' => '@FooAdmin/CRUD/list.html.twig',
                    'show' => '@FooAdmin/CRUD/show.html.twig',
                    'edit' => '@FooAdmin/CRUD/edit.html.twig',
                ],
                'second_theme' => [
                    'list' => '@FooAdmin/CRUD_v2/list.html.twig',
                    'show' => '@FooAdmin/CRUD_v2/show.html.twig',
                    'edit' => '@FooAdmin/CRUD_v2/edit.html.twig',
                ],
            ]
        );
    }

    public function testGetTemplates(): void
    {
        $templates = [
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        static::assertSame($templates, $this->templateRegistry->getTemplates());

        $templates = [
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        static::assertSame($templates, $this->templateRegistry->getTemplates('default'));

        $templates = [
            'list' => '@FooAdmin/CRUD_v2/list.html.twig',
            'show' => '@FooAdmin/CRUD_v2/show.html.twig',
            'edit' => '@FooAdmin/CRUD_v2/edit.html.twig',
        ];

        static::assertSame($templates, $this->templateRegistry->getTemplates('second_theme'));
    }

    public function testThrowExceptionIfTheTemplateDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Template named "foo" doesn\'t exist.');

        static::assertFalse($this->templateRegistry->hasTemplate('foo'));

        $this->templateRegistry->getTemplate('foo');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Template named "foo" doesn\'t exist for "second_theme" theme.');

        static::assertFalse($this->templateRegistry->hasTemplate('foo', 'second_theme'));

        $this->templateRegistry->getTemplate('foo');
    }
}
