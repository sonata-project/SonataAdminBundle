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

use Sonata\AdminBundle\Request\ParamConverter\AdminParamConverter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

/*
 * NEXT_MAJOR: Remove this file.
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.admin.param_converter', AdminParamConverter::class)
            ->tag('request.param_converter', ['converter' => 'sonata_admin'])
            ->args([
                new ReferenceConfigurator('sonata.admin.request.fetcher'),
            ]);
};
