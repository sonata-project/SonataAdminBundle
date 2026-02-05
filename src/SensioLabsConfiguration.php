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

namespace SensioLabs\AdminBundle;

/**
 * @phpstan-type SensioLabsConfigurationOptions = array{
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
 *     sort_admins: bool,
 *     stylesheets: list<string>,
 *     use_select2: bool,
 *     use_stickyforms: bool
 * }
 */
final class SensioLabsConfiguration
{
    /**
     * @param array<string, mixed> $options
     *
     * @phpstan-param SensioLabsConfigurationOptions $options
     */
    public function __construct(
        private string $title,
        private string $logo,
        private array $options,
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
