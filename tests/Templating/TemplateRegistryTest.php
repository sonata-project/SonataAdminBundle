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

class TemplateRegistryTest extends TestCase
{
    /**
     * @var TemplateRegistry
     */
    private $templateRegistry;

    protected function setUp(): void
    {
        $this->templateRegistry = new TemplateRegistry();
    }

    public function testGetTemplates(): void
    {
        $this->assertSame([], $this->templateRegistry->getTemplates());

        $templates = [
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates);
        $this->assertSame($templates, $this->templateRegistry->getTemplates());
    }

    public function testSetTemplate(): void
    {
        $this->assertFalse($this->templateRegistry->hasTemplate('edit'));

        $this->templateRegistry->setTemplate('edit', '@FooAdmin/CRUD/edit.html.twig');
        $this->templateRegistry->setTemplate('show', '@FooAdmin/CRUD/show.html.twig');

        $this->assertTrue($this->templateRegistry->hasTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));
        $this->assertTrue($this->templateRegistry->hasTemplate('show'));
        $this->assertSame('@FooAdmin/CRUD/show.html.twig', $this->templateRegistry->getTemplate('show'));
    }

    public function testSetTemplates(): void
    {
        $this->assertFalse($this->templateRegistry->hasTemplate('edit'));

        $templates = [
            'list' => '@FooAdmin/CRUD/list.html.twig',
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates);

        $this->assertTrue($this->templateRegistry->hasTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));
        $this->assertTrue($this->templateRegistry->hasTemplate('show'));
        $this->assertSame('@FooAdmin/CRUD/show.html.twig', $this->templateRegistry->getTemplate('show'));
    }

    public function testThrowExceptionIfTheTemplateDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Template named "edit" doesn\'t exist.');

        $this->assertFalse($this->templateRegistry->hasTemplate('edit'));

        $this->templateRegistry->getTemplate('edit');
    }
}
