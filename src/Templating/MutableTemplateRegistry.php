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
    public function setTemplates(array $templates, bool $override = true): void
    {
        if ($override) {
            $this->templates = $templates + $this->templates;

            return;
        }

        $this->templates = $this->templates + $templates;
    }

    public function setTemplate(string $name, string $template, bool $override = true): void
    {
        if (!$override && $this->hasTemplate($name)) {
            return;
        }

        $this->templates[$name] = $template;
    }
}
