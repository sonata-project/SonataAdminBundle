<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SensioLabs\AdminBundle\User\Mailer\Mailer;
use SensioLabs\AdminBundle\User\Mailer\MailerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.user.mailer', Mailer::class)
            ->args([
                service('mailer'),
                service('twig'),
                service('router'),
                param('sensiolabs.admin.user.resetting.from_email'),
                param('sensiolabs.admin.user.resetting.email_template'),
            ])
        ->alias(MailerInterface::class, 'sensiolabs.admin.user.mailer');
};
