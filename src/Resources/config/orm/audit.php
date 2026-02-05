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

use SensioLabs\AdminBundle\Block\ORM\AuditBlockService;
use SensioLabs\AdminBundle\Model\ORM\AuditReader;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.audit.orm.reader', AuditReader::class)
            ->public()
            ->tag('sensiolabs.admin.audit_reader')
            ->args([
                service('simplethings_entityaudit.reader')->ignoreOnInvalid(),
            ])

        ->set('sensiolabs.admin_doctrine_orm.block.audit', AuditBlockService::class)
            ->tag('sensiolabs.block')
            ->args([
                service('twig'),
                service('simplethings_entityaudit.reader')->ignoreOnInvalid(),
            ]);
};
