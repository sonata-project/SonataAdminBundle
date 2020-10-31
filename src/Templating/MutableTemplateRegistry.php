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
    // NEXT_MAJOR: change method declaration for new one
    // public function setTemplates(array $templates): void
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    // NEXT_MAJOR: change method declaration for new one
    // public function setTemplate(string $name, string $template): void
    public function setTemplate($name, $template)
    {
        $this->templates[$name] = $template;
    }
}
