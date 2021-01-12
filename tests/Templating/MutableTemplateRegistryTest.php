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
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

final class MutableTemplateRegistryTest extends TestCase
{
    use ExpectDeprecationTrait;

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
        $this->assertSame($templates, $this->templateRegistry->getTemplates());
    }

    /**
     * @group legacy
     */
    public function testGetTemplateAfterSetTemplate(): void
    {
        $this->templateRegistry->setTemplate('edit', '@FooAdmin/CRUD/edit.html.twig');

        $this->assertTrue($this->templateRegistry->hasTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));

        $this->assertFalse($this->templateRegistry->hasTemplate('nonexist_template'));
        // NEXT_MAJOR: remove this line
        $this->expectDeprecation('Passing a nonexistent template name as argument 1 to Sonata\AdminBundle\Templating\AbstractTemplateRegistry::getTemplate() is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0.');
        $this->assertNull($this->templateRegistry->getTemplate('nonexist_template'));
        // NEXT_MAJOR: Remove previous assertion, the "@group" annotations and uncomment the following line
        // $this->assertFalse($this->templateRegistry->hasTemplate('nonexist_template'));
    }

    /**
     * @group legacy
     */
    public function testGetTemplateAfterSetTemplates(): void
    {
        $templates = [
            'show' => '@FooAdmin/CRUD/show.html.twig',
            'edit' => '@FooAdmin/CRUD/edit.html.twig',
        ];

        $this->templateRegistry->setTemplates($templates);

        $this->assertTrue($this->templateRegistry->hasTemplate('edit'));
        $this->assertSame('@FooAdmin/CRUD/edit.html.twig', $this->templateRegistry->getTemplate('edit'));
        // NEXT_MAJOR: remove this line
        $this->expectDeprecation('Passing a nonexistent template name as argument 1 to Sonata\AdminBundle\Templating\AbstractTemplateRegistry::getTemplate() is deprecated since sonata-project/admin-bundle 3.52 and will throw an exception in 4.0.');
        $this->assertNull($this->templateRegistry->getTemplate('nonexist_template'));
        $this->assertFalse($this->templateRegistry->hasTemplate('nonexist_template'));
        // NEXT_MAJOR: Remove previous assertion, the "@group" annotations and uncomment the following line
        // $this->assertFalse($this->templateRegistry->hasTemplate('nonexist_template'));
    }
}
