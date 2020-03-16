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

namespace Sonata\AdminBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\SonataAdminExtension;
use Sonata\AdminBundle\Tests\Fixtures\DependencyInjection\DummySonataAdminExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AbstractSonataAdminExtensionTest extends TestCase
{
    public function testLoadIntlTemplates(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', []);
        $container->prependExtensionConfig('sonata_admin', ['use_intl_templates' => true]);
        $extension = new SonataAdminExtension();
        $dummyExtension = new DummySonataAdminExtension();
        $container->registerExtension($dummyExtension);
        $container->registerExtension($extension);
        $extension->prepend($container);
        $dummyExtension->load([], $container);

        $this->assertSame('@SonataAdmin/CRUD/Intl/list_date.html.twig', $dummyExtension->configs[0]['templates']['types']['list']['date']);
    }
}
