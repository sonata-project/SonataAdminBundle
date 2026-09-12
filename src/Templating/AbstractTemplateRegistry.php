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
     *
     * NEXT_MAJOR: Remove this property.
     */
    protected $templates = [];

    /**
     * @var array<string, array<string, string>>
     */
    protected $themedTemplates = [];

    /**
     * @param array<string, string>                $templates       ['name' => 'file_path.html.twig']
     * @param array<string, array<string, string>> $themedTemplates ['theme_name' => ['name' => 'file_path.html.twig']]
     */
    public function __construct(array $templates = [], array $themedTemplates = [])
    {
        $this->themedTemplates = $themedTemplates;
        $this->themedTemplates[TemplateRegistryInterface::DEFAULT_THEME] = $templates + ($this->themedTemplates[TemplateRegistryInterface::DEFAULT_THEME] ?? []);
        $this->templates = $this->themedTemplates[TemplateRegistryInterface::DEFAULT_THEME];
    }

    final public function getTemplates(string $theme = TemplateRegistryInterface::DEFAULT_THEME): array
    {
        // NEXT_MAJOR: Remove if section.
        if (TemplateRegistryInterface::DEFAULT_THEME === $theme) {
            return $this->templates;
        }

        return $this->themedTemplates[$theme] ?? [];
    }


    final public function hasTemplate(string $name, string $theme = TemplateRegistryInterface::DEFAULT_THEME): bool
    {
        // NEXT_MAJOR: Remove if section.
        if (TemplateRegistryInterface::DEFAULT_THEME === $theme) {
            return isset($this->templates[$name]);
        }

        return isset($this->themedTemplates[$theme][$name]);
    }

    /**
     * NEXT_MAJOR: remove phpstan-ignore line and if section.
     */
    final public function getTemplate(string $name, string $theme = TemplateRegistryInterface::DEFAULT_THEME): string
    {
        if (TemplateRegistryInterface::DEFAULT_THEME === $theme) {
            if ($this->hasTemplate($name)) {
                return $this->templates[$name];
            }

            throw new \InvalidArgumentException(\sprintf('Template named "%s" doesn\'t exist.', $name));
        }

        if ($this->hasTemplate($name, $theme)) {
            return $this->themedTemplates[$theme][$name];
        }

        throw new \InvalidArgumentException(\sprintf('Template named "%s" doesn\'t exist for "%s" theme.', $name, $theme));
    }
}
