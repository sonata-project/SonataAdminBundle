<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Admin;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Extension\ExtensionEmitter;
use Sonata\AdminBundle\Extension\ExtensionNotifier;
use Sonata\AdminBundle\Extension\ExtensionProcessor;
use Sonata\AdminBundle\Extension\ExtensionProvider;

/**
 * @internal
 *
 * @author Marko Kunic <kunicmarko20@gmail.com>
 */
trait SetExtensionEmitterTrait
{
    private function setExtensionEmitter(AdminInterface $admin, array $extensions = [])
    {
        $admin->setExtensionEmitter(
            new ExtensionEmitter(
                new ExtensionNotifier($extensionProvider = new ExtensionProvider()),
                new ExtensionProcessor($extensionProvider)
            )
        );

        $extensionProvider->setExtensions($extensions);
    }
}
