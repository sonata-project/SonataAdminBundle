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

if (!class_exists(\Sonata\Twig\Templating\MutableTemplateRegistry::class, false)) {
    @trigger_error(
        'The '.__NAMESPACE__.'\MutableTemplateRegistry class is deprecated since version 3.x and will be removed in 4.0.'
        .' Use Sonata\Twig\Templating\MutableTemplateRegistry instead.',
        E_USER_DEPRECATED
    );
}

class_alias(
    \Sonata\Twig\Templating\MutableTemplateRegistry::class,
    __NAMESPACE__.'\MutableTemplateRegistry'
);

if (false) {
    /**
     * @author Wojciech Błoszyk <wbloszyk@gmail.com>
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
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
}
