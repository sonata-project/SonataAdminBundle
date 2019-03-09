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

namespace Sonata\AdminBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\TemplateRegistry;

class TemplateRegistryTest extends TestCase
{
    public function testGetTemplates(): void
    {
        $templates = [
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $templateRegistry = new TemplateRegistry($templates);

        $this->assertSame($templates, $templateRegistry->getTemplates());
    }

    public function testGetTemplate1(): void
    {
        $templateRegistry = new TemplateRegistry([
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
        ]);

        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $templateRegistry->getTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/show.html.twig', $templateRegistry->getTemplate('show'));
    }

    public function testGetTemplate2(): void
    {
        $templateRegistry = new TemplateRegistry([
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
        ]);

        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $templateRegistry->getTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/show.html.twig', $templateRegistry->getTemplate('show'));

        $this->assertFalse($templateRegistry->hasTemplate('list'));

        $templateRegistry->setTemplate('list', '@FooAdmin/CRUD/list.html.twig');

        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $templateRegistry->getTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/show.html.twig', $templateRegistry->getTemplate('show'));
        $this->assertSame('@FooAdmin/CRUD/list.html.twig', $templateRegistry->getTemplate('list'));
    }

    public function testGetTemplate3(): void
    {
        $templateRegistry = new TemplateRegistry([]);

        $this->assertFalse($templateRegistry->hasTemplate('edit'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Template "edit" not found in template registry');

        $templateRegistry->getTemplate('edit');
    }

    public function testHasTemplate(): void
    {
        $templateRegistry = new TemplateRegistry([
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ]);

        $this->assertFalse($templateRegistry->hasTemplate('list'));
        $this->assertTrue($templateRegistry->hasTemplate('show'));
        $this->assertTrue($templateRegistry->hasTemplate('edit'));
    }
}
