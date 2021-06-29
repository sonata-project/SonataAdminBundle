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

        self::assertSame($templates, $this->templateRegistry->getTemplates());
    }

    public function testThrowExceptionIfTheTemplateDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Template named "foo" doesn\'t exist.');

        self::assertFalse($this->templateRegistry->hasTemplate('foo'));

        $this->templateRegistry->getTemplate('foo');
    }
}
