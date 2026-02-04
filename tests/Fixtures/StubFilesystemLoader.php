<?php

declare(strict_types=1);

/*
 * This file is part of sensiolabs-de/admin-bundle.
 *
 * (c) SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\AdminBundle\Tests\Fixtures;

use Twig\Loader\FilesystemLoader;

final class StubFilesystemLoader extends FilesystemLoader
{
    /**
     * @param string $name
     * @param bool   $throw
     */
    protected function findTemplate($name, $throw = true): ?string
    {
        // strip away bundle name
        $parts = explode(':', $name);

        return parent::findTemplate(end($parts), $throw);
    }
}
