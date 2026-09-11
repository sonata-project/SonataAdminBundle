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

/**
 * @author Wojciech Błoszyk <wbloszyk@gmail.com>
 */
final class MutableTemplateRegistry extends AbstractTemplateRegistry implements MutableTemplateRegistryInterface
{
    /**
     * @phpstan-ignore arguments.count
     * NEXT_MAJOR: remove phpstan-ignore and if section
     */
    public function setTemplates(array $templates, string $theme = TemplateRegistryInterface::DEFAULT_THEME): void
    {
        // NEXT_MAJOR: remove if
        if ('default' === $theme) {
            $this->templates = $templates + $this->templates;
        }

        $this->themedTemplates[$theme] = $templates + ($this->themedTemplates[$theme] ?? []);
    }

    /**
     * @phpstan-ignore arguments.count
     * NEXT_MAJOR: remove phpstan-ignore and if section
     */
    public function setTemplate(string $name, string $template, string $theme = TemplateRegistryInterface::DEFAULT_THEME): void
    {
        if (TemplateRegistryInterface::DEFAULT_THEME === $theme) {
            $this->templates[$name] = $template;
        }

        $this->themedTemplates[$theme][$name] = $template;
    }
}
