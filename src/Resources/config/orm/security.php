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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SensioLabs\AdminBundle\Util\ORM\ObjectAclManipulator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()

        ->set('sensiolabs.admin.manipulator.acl.object.orm.class', ObjectAclManipulator::class);

    $containerConfigurator->services()

        ->set('sensiolabs.admin.manipulator.acl.object.orm', (string) param('sensiolabs.admin.manipulator.acl.object.orm.class'))
            ->public()
            ->args([
                service('doctrine'),
            ]);
};
