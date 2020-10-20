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

use Sonata\AdminBundle\Helper\CsrfTokenManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {

    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1

    $csrfManager = new ReferenceConfigurator('security.csrf.token_manager');
    $csrfManager->nullOnInvalid();

    $containerConfigurator->services()

        ->set('sonata.admin.helper.csrf_manager', CsrfTokenManager::class)
            ->public()
            ->args([
                new ReferenceConfigurator('request_stack'),
                $csrfManager,
            ]);
};
