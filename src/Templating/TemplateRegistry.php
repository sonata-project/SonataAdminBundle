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

if (!class_exists(\Sonata\Twig\Templating\TemplateRegistry::class, false)) {
    @trigger_error(
        'The '.__NAMESPACE__.'\TemplateRegistry class is deprecated since version 3.x and will be removed in 4.0.'
        .' Use Sonata\Twig\Templating\TemplateRegistry instead.',
        E_USER_DEPRECATED
    );
}

class_alias(
    \Sonata\Twig\Templating\TemplateRegistry::class,
    __NAMESPACE__.'\TemplateRegistry'
);

if (false) {
    /**
     * @author Timo Bakx <timobakx@gmail.com>
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
     *
     * NEXT_MAJOR: remove `MutableTemplateRegistryInterface` implementation.
     */
    final class TemplateRegistry extends AbstractTemplateRegistry implements MutableTemplateRegistryInterface
    {
        /**
         * NEXT_MAJOR: remove this method.
         *
         * @deprecated since version sonata-project/admin-bundle 3.39.0 and will be removed in 4.0. Use Sonata\AdminBundle\Templating\MutableTemplateRegistry instead.
         */
        public function setTemplates(array $templates)
        {
            $this->templates = $templates;

            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.39 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }

        /**
         * NEXT_MAJOR: remove this method.
         *
         * @deprecated since version sonata-project/admin-bundle 3.39.0 and will be removed in 4.0. Use Sonata\AdminBundle\Templating\MutableTemplateRegistry instead.
         */
        public function setTemplate($name, $template)
        {
            $this->templates[$name] = $template;

            @trigger_error(sprintf(
                'Method "%s()" is deprecated since sonata-project/admin-bundle 3.39 and will be removed in 4.0.',
                __METHOD__
            ), E_USER_DEPRECATED);
        }
    }
}
