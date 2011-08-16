<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddGuesserCompilerPass;
use Sonata\AdminBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;

class SonataAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
        $container->addCompilerPass(new AddGuesserCompilerPass());
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
    }
}
