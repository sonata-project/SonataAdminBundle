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

if (!class_exists(\Sonata\Twig\Templating\MutableTemplateRegistryAwareInterface::class, false)) {
    @trigger_error(
        'The '.__NAMESPACE__.'\MutableTemplateRegistryAwareInterface class is deprecated since version 3.x and will be removed in 4.0.'
        .' Use Sonata\Twig\Templating\MutableTemplateRegistryAwareInterface instead.',
        E_USER_DEPRECATED
    );
}

class_alias(
    \Sonata\Twig\Templating\MutableTemplateRegistryAwareInterface::class,
    __NAMESPACE__.'\MutableTemplateRegistryAwareInterface'
);

if (false) {
    /**
     * @author Wojciech Błoszyk <wbloszyk@gmail.com>
     *
     * @deprecated since sonata-project/admin-bundle 3.x, to be removed in 4.0.
     *
     * @method MutableTemplateRegistryInterface getTemplateRegistry()
     * @method bool                             hasTemplateRegistry()
     * @method void                             setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry)
     */
    interface MutableTemplateRegistryAwareInterface
    {
        // NEXT_MAJOR: uncomment this method in 4.0
        //public function getTemplateRegistry(): MutableTemplateRegistryInterface;

        // NEXT_MAJOR: uncomment this method in 4.0
        //public function hasTemplateRegistry(): bool;

        // NEXT_MAJOR: uncomment this method in 4.0
        //public function setTemplateRegistry(MutableTemplateRegistryInterface $templateRegistry): void;
    }
}
