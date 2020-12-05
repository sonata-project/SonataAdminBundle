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
 * @psalm-type SonataConfigurationOptions = array{
 *  confirm_exit: bool,
 *  default_group: string,
 *  default_icon: string,
 *  default_label_catalogue: string,
 *  dropdown_number_groups_per_colums: int,
 *  form_type: string,
 *  html5_validate: bool,
 *  javascripts: list<string>,
 *  js_debug: bool,
 *  legacy_twig_text_extension: bool,
 *  lock_protection: bool,
 *  mosaic_background: string,
 *  pager_links: ?int,
 *  role_admin: string,
 *  role_super_admin: string,
 *  search: bool,
 *  skin: string,
 *  sort_admins: bool,
 *  stylesheets: list<string>,
 *  title_mode: 'single_text'|'single_image'|'both',
 *  use_bootlint: bool,
 *  use_icheck: bool,
 *  use_select2: bool,
 *  use_stickyforms: bool
 * }
 */
final class SonataConfiguration
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var array
     * @psalm-var SonataConfigurationOptions $options
     * @phpstan-var array<string, mixed>
     */
    private $options;

    /**
     * @var array
     * @psalm-param SonataConfigurationOptions $options
     * @phpstan-param array<string, mixed> $options
     */
    public function __construct(string $title, string $logo, array $options)
    {
        $this->title = $title;
        $this->logo = $logo;
        $this->options = $options;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }
}
