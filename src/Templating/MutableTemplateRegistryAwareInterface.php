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
 * NEXT_MAJOR: Remove this interface and move the method to the TaggedAdminInterface.
 *
 * @author Wojciech Błoszyk <wbloszyk@gmail.com>
 *
 * @deprecated since sonata-project/sonata-admin 3.x
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

    /**
     * NEXT_MAJOR: remove this method declaration with docblock and uncomment code below.
     *
     * @param string $name
     * @param string $template
     */
    public function setTemplate($name, $template);

    //public function setTemplate(string $name, string $template);

    /**
     * NEXT_MAJOR: remove this method declaration and uncomment code below.
     *
     * @param array<string, string> $templates
     */
    public function setTemplates(array $templates);

    //public function setTemplates(array $templates): void;
}
