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
    public function setTemplates(array $templates /* ,string $layout */): void
    {
        if (\func_num_args() < 2) {
            @trigger_error(
                'Not passing the "string $layout" argument explicitly is deprecated since sonata-project/admin-bundle x.y and will be required in 5.0.',
                \E_USER_DEPRECATED
            );

            $layout = 'default';
        } else {
            $layout = func_get_arg(1);
        }

        // Keep BC compatibility
        if ('default' === $layout) {
            $this->templates = $templates + $this->templates;
        }
        // NEXT_MAJOR: Remove code before this comment
        $this->layoutTemplates[$layout] = $templates + ($this->layoutTemplates[$layout] ?? []);
    }

    public function setTemplate(string $name, string $template /* ,string $layout */): void
    {
        if (\func_num_args() < 3) {
            @trigger_error(
                'Not passing the "string $layout" argument explicitly is deprecated since sonata-project/admin-bundle x.y and will be required in 5.0.',
                \E_USER_DEPRECATED
            );

            $layout = 'default';
        } else {
            $layout = func_get_arg(2);
        }

        // Keep BC compatibility
        if ('default' === $layout) {
            $this->templates[$name] = $template;
        }

        // NEXT_MAJOR: Remove code before this comment
        $this->layoutTemplates[$layout][$name] = $template;
    }
}
