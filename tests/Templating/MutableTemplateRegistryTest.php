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
    /**
     * @var MutableTemplateRegistryInterface
     */
    private $templateRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->templateRegistry = new MutableTemplateRegistry(['list' => '@FooAdmin/CRUD/list.html.twig']);
    }

    public function testGetTemplates(): void
    {
        $this->assertSame(['list' => '@FooAdmin/CRUD/list.html.twig'], $this->templateRegistry->getTemplates());

        $templates = [
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates);
        $this->assertSame($templates + ['list' => '@FooAdmin/CRUD/list.html.twig'], $this->templateRegistry->getTemplates());
    }

    public function testGetTemplateAfterSetTemplate(): void
    {
        $this->templateRegistry->setTemplate('edit', '@FooAdmin/CRUD/edit.html.twig');

        $this->assertTrue($this->templateRegistry->hasTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));

        $this->assertFalse($this->templateRegistry->hasTemplate('nonexist_template'));
    }

    public function testGetTemplateAfterSetTemplates(): void
    {
        $templates = [
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates);

        $this->assertTrue($this->templateRegistry->hasTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));

        $this->assertFalse($this->templateRegistry->hasTemplate('nonexist_template'));
    }
}
