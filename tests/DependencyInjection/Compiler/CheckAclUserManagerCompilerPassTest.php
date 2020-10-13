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

namespace Sonata\AdminBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\CheckAclUserManagerCompilerPass;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * NEXT_MAJOR: Remove this test.
 *
 * @group legacy
 */
final class CheckAclUserManagerCompilerPassTest extends AbstractCompilerPassTestCase
{
    use ExpectDeprecationTrait;

    public function testTriggersADeprecationIfItAclUserManagerIsNotProperlyConfigured(): void
    {
        $aclUserManager = new Definition(\stdClass::class);
        $this->setDefinition('acl_user_manager', $aclUserManager);
        $this->setParameter('sonata.admin.security.acl_user_manager', 'acl_user_manager');
        $this->setParameter('sonata.admin.security.fos_user_autoconfigured', false);

        $this->expectDeprecation('Configuring the service in sonata_admin.security.acl_user_manager without implementing "Sonata\AdminBundle\Util\AdminAclUserManagerInterface" is deprecated since sonata-project/admin-bundle 3.x and will throw an "InvalidArgumentException" exception in 4.0.');

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CheckAclUserManagerCompilerPass());
    }
}
