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

if (!class_exists(\Sonata\Twig\Templating\MutableTemplateRegistryInterface::class, false)) {
    @trigger_error(
        'The '.__NAMESPACE__.'\MutableTemplateRegistryInterface class is deprecated since version 3.x and will be removed in 4.0.'
        .' Use Sonata\Twig\Templating\MutableTemplateRegistryInterface instead.',
        E_USER_DEPRECATED
    );
}

class_alias(
    \Sonata\Twig\Templating\MutableTemplateRegistryInterface::class,
    __NAMESPACE__.'\MutableTemplateRegistryInterface'
);

if (false) {
    /**
     * @author Timo Bakx <timobakx@gmail.com>
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
     */
    interface MutableTemplateRegistryInterface extends TemplateRegistryInterface
    {
        /**
         * NEXT_MAJOR: remove this method declaration with docblock and uncomment code below.
         *
         * @param array<string, string> $templates 'name' => 'file_path.html.twig'
         */
        public function setTemplates(array $templates);

        ///**
        // * @param array<string, string> $templates 'name' => 'file_path.html.twig'
        // */
        //public function setTemplates(array $templates): void;

        /**
         * NEXT_MAJOR: remove this method declaration with docblock and uncomment code below.
         *
         * @param string $name
         * @param string $template
         *
         * @return void
         */
        public function setTemplate($name, $template);

        //public function setTemplate(string $name, string $template): void;
    }
}
