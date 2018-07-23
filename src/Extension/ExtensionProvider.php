<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Extension;

use Sonata\AdminBundle\Admin\Extension\AdminExtensionInterface;
use Sonata\AdminBundle\Extension\Event\EventInterface;

/**
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
final class ExtensionProvider
{
    /**
     * @var array
     */
    private $extensions = [];

    /**
     * @return iterable
     */
    public function getListenersForEvent(EventInterface $event)
    {
        $extensionInterface = EventInterfaceMap::get($event);

        $extensions = [];

        foreach ($this->extensions as $extension) {
            if (is_subclass_of($extension, $extensionInterface)) {
                $extensions[] = $extension;
            }
        }

        return $extensions;
    }

    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }

    public function addExtension(AdminExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }
}
