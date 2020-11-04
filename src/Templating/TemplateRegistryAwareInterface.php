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
 *
 * @method TemplateRegistryInterface getTemplateRegistry()
 * @method bool                      hasTemplateRegistry()
 * @method void                      setTemplateRegistry(TemplateRegistryInterface $templateRegistry)
 */
interface TemplateRegistryAwareInterface
{
    // NEXT_MAJOR: uncomment this method in 4.0
    //public function getTemplateRegistry(): TemplateRegistryInterface;

    // NEXT_MAJOR: uncomment this method in 4.0
    //public function hasTemplateRegistry(): bool;

    // NEXT_MAJOR: uncomment this method in 4.0
    //public function setTemplateRegistry(TemplateRegistryInterface $templateRegistry): void;
}
