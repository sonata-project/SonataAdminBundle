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

use Sonata\AdminBundle\Twig\Extension\DeprecatedTextExtension;
use Sonata\AdminBundle\Twig\Extension\StringExtension;
use Sonata\AdminBundle\Util\BCDeprecationParameters;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.string.twig.extension', StringExtension::class)
            ->tag('twig.extension')
            ->args([
                null,
            ])

        // NEXT_MAJOR: Remove this service.
        ->set('sonata.deprecated_text.twig.extension', DeprecatedTextExtension::class)
            ->tag('twig.extension')
            ->deprecate(...BCDeprecationParameters::forConfig(
                'The "%service_id%" service is deprecated since sonata-project/admin-bundle 3.70. You should stop using it, as it will be removed in 4.0.',
                '3.70'
            ));
};
