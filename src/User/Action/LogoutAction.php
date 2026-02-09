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

namespace SensioLabs\AdminBundle\User\Action;

/**
 * This action is intercepted by the Symfony firewall's logout handler.
 * It should never actually be executed.
 */
final readonly class LogoutAction
{
    public function __invoke(): never
    {
        throw new \RuntimeException('You must configure the logout path to be handled by the firewall using logout in your security firewall configuration.');
    }
}
