<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\CoreBundle\Form\FormHelper;

/**
 * Test for SonataAdminBundle.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class SonataAdminBundleTest extends AbstractContainerBuilderTestCase
{
    /**
     * @var SonataAdminBundle
     */
    private $bundle;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->bundle = new SonataAdminBundle();
    }

    public function testBuild()
    {
        $this->bundle->build($this->container);

        $passes = $this->container->getCompilerPassConfig()->getPasses();
        $this->assertCount(5, $passes);
        $this->assertInstanceOf(
            'Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass',
            $passes[1]
        );
        $this->assertInstanceOf(
            'Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass',
            $passes[2]
        );
        $this->assertInstanceOf(
            'Sonata\AdminBundle\DependencyInjection\Compiler\ExtensionCompilerPass',
            $passes[3]
        );
        $this->assertInstanceOf(
            'Sonata\AdminBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass',
            $passes[4]
        );
    }

    public function testBoot()
    {
        $this->bundle->boot();

        $this->assertContains('Sonata\AdminBundle\Form\Type\AdminType', FormHelper::getFormTypeMapping());
        $this->assertContains('sonata.admin.form.extension.field', FormHelper::getFormExtensionMapping()['form']);
    }

    public function testGetContainerExtension()
    {
        $this->assertInstanceOf('Sonata\AdminBundle\SonataAdminBundle', $this->bundle);
        $this->assertInstanceOf('Sonata\AdminBundle\DependencyInjection\SonataAdminExtension', $this->bundle->getContainerExtension());
    }
}
