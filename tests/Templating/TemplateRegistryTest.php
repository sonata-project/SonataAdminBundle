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

namespace SensioLabs\AdminBundle\Tests\Templating;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\Templating\TemplateRegistry;

final class TemplateRegistryTest extends TestCase
{
    private TemplateRegistry $templateRegistry;

    protected function setUp(): void
    {
        $this->templateRegistry = new TemplateRegistry([
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ]);
    }

    public function testGetTemplates(): void
    {
        $templates = [
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        static::assertSame($templates, $this->templateRegistry->getTemplates());
    }

    public function testThrowExceptionIfTheTemplateDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Template named "foo" doesn\'t exist.');

        static::assertFalse($this->templateRegistry->hasTemplate('foo'));

        $this->templateRegistry->getTemplate('foo');
    }
}
