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

use Knp\Menu\MenuItem;
use Sonata\AdminBundle\Menu\Matcher\Voter\ActiveVoter;
use Sonata\AdminBundle\Menu\Matcher\Voter\AdminVoter;
use Sonata\AdminBundle\Menu\MenuBuilder;
use Sonata\AdminBundle\Menu\Provider\GroupMenuProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.admin.menu_builder', MenuBuilder::class)
            ->args([
                service('sonata.admin.pool'),
                service('knp_menu.factory'),
                service('knp_menu.menu_provider'),
                service('event_dispatcher'),
            ])

        ->set('sonata.admin.sidebar_menu', MenuItem::class)
            ->share(false)
            ->tag('knp_menu.menu', ['alias' => 'sonata_admin_sidebar'])
            ->factory([
                service('sonata.admin.menu_builder'),
                'createSidebarMenu',
            ])

        ->set('sonata.admin.menu.group_provider', GroupMenuProvider::class)
            ->tag('knp_menu.provider')
            ->args([
                service('knp_menu.factory'),
                service('sonata.admin.pool'),
                service('security.authorization_checker'),
            ])

        ->set('sonata.admin.menu.matcher.voter.admin', AdminVoter::class)
            ->tag('knp_menu.voter')
            ->args([
                service('request_stack'),
            ])

        ->set('sonata.admin.menu.matcher.voter.active', ActiveVoter::class)
            ->tag('knp_menu.voter');
};
