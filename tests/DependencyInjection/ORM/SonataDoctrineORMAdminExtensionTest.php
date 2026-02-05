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

namespace SensioLabs\AdminBundle\Tests\DependencyInjection\ORM;

use PHPUnit\Framework\TestCase;
use SensioLabs\AdminBundle\DependencyInjection\ORM\SonataDoctrineORMAdminExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SonataDoctrineORMAdminExtensionTest extends TestCase
{
    public function testEntityManagerSetFactory(): void
    {
        $configuration = new ContainerBuilder();
        $configuration->setParameter('kernel.bundles', ['SimpleThingsEntityAuditBundle' => true]);
        $loader = new SonataDoctrineORMAdminExtension();
        $loader->load([], $configuration);

        $definition = $configuration->getDefinition('sonata.admin.entity_manager');

        static::assertNotNull($definition->getFactory());
        static::assertNotFalse($configuration->getParameter('sonata_doctrine_orm_admin.audit.force'));
    }
}
