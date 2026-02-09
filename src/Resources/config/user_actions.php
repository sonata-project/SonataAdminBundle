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

use SensioLabs\AdminBundle\User\Action\CheckEmailAction;
use SensioLabs\AdminBundle\User\Action\CheckLoginAction;
use SensioLabs\AdminBundle\User\Action\LoginAction;
use SensioLabs\AdminBundle\User\Action\LogoutAction;
use SensioLabs\AdminBundle\User\Action\RequestAction;
use SensioLabs\AdminBundle\User\Action\ResetAction;
use SensioLabs\AdminBundle\User\Action\SendEmailAction;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sensiolabs.admin.user.action.login', LoginAction::class)
            ->public()
            ->args([
                service('twig'),
                service('security.authentication_utils'),
                service('sensiolabs.admin.pool'),
                service('sensiolabs.admin.global_template_registry'),
                service('security.token_storage'),
                service('router'),
            ])

        ->set('sensiolabs.admin.user.action.check_login', CheckLoginAction::class)
            ->public()

        ->set('sensiolabs.admin.user.action.logout', LogoutAction::class)
            ->public()

        ->set('sensiolabs.admin.user.action.request', RequestAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sensiolabs.admin.pool'),
                service('sensiolabs.admin.global_template_registry'),
                service('security.authorization_checker'),
                service('router'),
            ])

        ->set('sensiolabs.admin.user.action.send_email', SendEmailAction::class)
            ->public()
            ->args([
                service('sensiolabs.admin.user.manager'),
                service('sensiolabs.admin.user.mailer'),
                service('router'),
                param('sensiolabs.admin.user.resetting.ttl'),
            ])

        ->set('sensiolabs.admin.user.action.check_email', CheckEmailAction::class)
            ->public()
            ->args([
                service('twig'),
                service('sensiolabs.admin.pool'),
                service('sensiolabs.admin.global_template_registry'),
                param('sensiolabs.admin.user.resetting.ttl'),
            ])

        ->set('sensiolabs.admin.user.action.reset', ResetAction::class)
            ->public()
            ->args([
                service('twig'),
                service('form.factory'),
                service('sensiolabs.admin.user.manager'),
                service('sensiolabs.admin.pool'),
                service('sensiolabs.admin.global_template_registry'),
                service('router'),
                param('sensiolabs.admin.user.resetting.ttl'),
            ]);
};
