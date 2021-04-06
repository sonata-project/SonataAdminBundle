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

use Sonata\AdminBundle\Util\BCDeprecationParameters;
use Sonata\Form\Validator\InlineValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        // NEXT_MAJOR: Remove this file.
        ->set('sonata.admin.validator.inline', InlineValidator::class)
            ->public()
            ->tag('validator.constraint_validator', ['alias' => 'sonata.admin.validator.inline'])
            ->args([
                new ReferenceConfigurator('service_container'),
                new ReferenceConfigurator('validator.validator_factory'),
            ])
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The service "%service_id%" is deprecated since sonata-project/admin-bundle 3.96 and will be removed in 4.0.',
                '3.96'
            ));
};
