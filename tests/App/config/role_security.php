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

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/*
 * The "role_security" environment uses the role security handler and a few users with different
 * permissions, for functional tests that need real access checks. AppKernel::build() also registers
 * an audit reader for the bar admin in this environment.
 */
return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('sonata_admin', [
        'security' => ['handler' => 'sonata.admin.security.handler.role'],
    ]);

    $containerConfigurator->extension('security', [
        // Every voter has to agree, so an object based voter can deny what a role grants.
        'access_decision_manager' => ['strategy' => 'unanimous'],
        'providers' => [
            'in_memory' => [
                'memory' => [
                    'users' => [
                        // May see the history of bars, except the one denied by LockedBarVoter.
                        'bar_auditor' => [
                            'password' => 'bar_auditor',
                            'roles' => ['ROLE_SONATA_BAR_ADMIN_HISTORY'],
                        ],
                    ],
                ],
            ],
        ],
    ]);
};
