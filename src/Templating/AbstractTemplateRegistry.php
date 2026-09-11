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

namespace Sonata\AdminBundle\Templating;

abstract class AbstractTemplateRegistry implements TemplateRegistryInterface
{
    /**
     * @var array<string, string>
     *
     * @deprecated since sonata-project/admin-bundle x.x, will be removed in x.x.
     * NEXT_MAJOR: remove this property
     */
    protected $templates = [];

    /**
     * @var array<string, array<string, string>>
     */
    protected $themedTemplates = [];

    /**
     * @param array<string, string>                $templates       ['template_name' => 'template']
     * @param array<string, array<string, string>> $themedTemplates ['theme_name' => ['template_name' => 'template']]
     */
    public function __construct(array $templates = [], array $themedTemplates = [])
    {
        $this->templates = $templates;
        $this->themedTemplates = $themedTemplates;
        $this->themedTemplates['default'] = $templates + ($this->themedTemplates['default'] ?? []);
    }

    /**
     * @phpstan-ignore arguments.count
     * NEXT_MAJOR: remove phpstan-ignore and if section
     */
    final public function getTemplates(string $theme = 'default'): array
    {
        if ('default' === $theme) {
            return $this->templates;
        }

        return $this->themedTemplates[$theme] ?? [];
    }

    /**
     * @phpstan-ignore arguments.count
     * NEXT_MAJOR: remove phpstan-ignore and if section
     */
    final public function hasTemplate(string $name, string $theme = 'default'): bool
    {
        if ('default' === $theme) {
            return isset($this->templates[$name]);
        }

        return isset($this->themedTemplates[$theme][$name]);
    }

    /**
     * @phpstan-ignore arguments.count
     * NEXT_MAJOR: remove phpstan-ignore and if section
     */
    final public function getTemplate(string $name, string $theme = 'default'): string
    {
        if ('default' === $theme) {
            if ($this->hasTemplate($name)) {
                return $this->templates[$name];
            }

            throw new \InvalidArgumentException(\sprintf('Template named "%s" doesn\'t exist.', $name));
        }

        /*
         * @phpstan-ignore arguments.count
         * NEXT_MAJOR: remove phpstan-ignore
         */
        if ($this->hasTemplate($name, $theme)) {
            return $this->themedTemplates[$theme][$name];
        }

        throw new \InvalidArgumentException(\sprintf('Template named "%s" doesn\'t exist for "%s" theme.', $name, $theme));
    }
}
