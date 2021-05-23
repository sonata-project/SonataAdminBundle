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

namespace Sonata\AdminBundle\Tests\Fixtures;

use Twig\Loader\FilesystemLoader;

final class StubFilesystemLoader extends FilesystemLoader
{
    /**
     * @param string $name
     * @param bool   $throw
     */
    protected function findTemplate($name, $throw = true)
    {
        // strip away bundle name
        $parts = explode(':', $name);

        return parent::findTemplate(end($parts), $throw);
    }
}
