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
    public function setTemplates(array $templates, string $theme = 'default'): void
    {
        // NEXT_MAJOR: remove if
        if ('default' === $theme) {
            $this->templates = $templates + $this->templates;
            $this->themedTemplates['default'] = $templates + $this->themedTemplates['default'];

            return;
        }

        $this->themedTemplates[$theme] = $templates + $this->themedTemplates[$theme];
    }

    public function setTemplate(string $name, string $template, string $theme = 'default'): void
    {
        // NEXT_MAJOR: remove if
        if ('default' === $theme) {
            $this->templates[$name] = $template;
            $this->themedTemplates['default'][$name] = $template;

            return;
        }

        $this->themedTemplates[$theme][$name] = $template;
    }
}
