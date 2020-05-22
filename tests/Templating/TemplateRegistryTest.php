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

    /**
     * @group legacy
     *
     * @expectedDeprecation Passing a nonexistent template name as argument 1 to Sonata\AdminBundle\Templating\TemplateRegistry::getTemplate() is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0.
     */
    public function testGetTemplate1(): void
    {
        $this->assertFalse($this->templateRegistry->hasTemplate('edit'));
        $this->assertNull($this->templateRegistry->getTemplate('edit'));
        // NEXT_MAJOR: Remove previous assertion, the "@group" and "@expectedDeprecation" annotations and uncomment the following line
        // $this->assertFalse($this->templateRegistry->hasTemplate('edit'));

        $this->templateRegistry->setTemplate('edit', '@FooAdmin/CRUD/edit.html.twig');
        $this->templateRegistry->setTemplate('show', '@FooAdmin/CRUD/show.html.twig');

        $this->assertTrue($this->templateRegistry->hasTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));
        $this->assertTrue($this->templateRegistry->hasTemplate('show'));
        $this->assertSame('@FooAdmin/CRUD/show.html.twig', $this->templateRegistry->getTemplate('show'));
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Passing a nonexistent template name as argument 1 to Sonata\AdminBundle\Templating\TemplateRegistry::getTemplate() is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0.
     */
    public function testGetTemplate2(): void
    {
        $this->assertFalse($this->templateRegistry->hasTemplate('edit'));
        $this->assertNull($this->templateRegistry->getTemplate('edit'));
        // NEXT_MAJOR: Remove previous assertion, the "@group" and "@expectedDeprecation" annotations and uncomment the following line
        // $this->assertFalse($this->templateRegistry->hasTemplate('edit'));

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
}
