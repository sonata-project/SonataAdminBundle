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

namespace Sonata\AdminBundle;

/**
 * @phpstan-type SonataConfigurationOptions = array{
 *     confirm_exit: bool,
 *     default_admin_route: string,
 *     default_group: string,
 *     default_icon: string,
 *     default_translation_domain: string,
 *     dropdown_number_groups_per_colums: int,
 *     form_type: 'standard'|'horizontal',
 *     html5_validate: bool,
 *     javascripts: list<string>,
 *     js_debug: bool,
 *     list_action_button_content: 'text'|'icon'|'all',
 *     lock_protection: bool,
 *     logo_content: 'text'|'icon'|'all',
 *     mosaic_background: string,
 *     pager_links: ?int,
 *     role_admin: string,
 *     role_super_admin: string,
 *     search: bool,
 *     skin: 'skin-black'|'skin-black-light'|'skin-blue'|'skin-blue-light'|'skin-green'|'skin-green-light'|'skin-purple'|'skin-purple-light'|'skin-red'|'skin-red-light'|'skin-yellow'|'skin-yellow-light',
 *     sort_admins: bool,
 *     stylesheets: list<string>,
 *     use_bootlint: bool,
 *     use_icheck: bool,
 *     use_select2: bool,
 *     use_stickyforms: bool
 * }
 */
final class SonataConfiguration
{
    /**
     * @param array<string, mixed> $options
     *
     * @phpstan-param SonataConfigurationOptions $options
     */
    public function __construct(
        private string $title,
        private string $logo,
        private array $options
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }
}
