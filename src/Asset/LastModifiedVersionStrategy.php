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

namespace Sonata\AdminBundle\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class LastModifiedVersionStrategy implements VersionStrategyInterface
{
    public function __construct(
        private string $projectDir,
        private string $publicDir,
    ) {
    }

    public function getVersion(string $path): string
    {
        $localPath = \sprintf('%s%s/%s', $this->projectDir, $this->publicDir, $path);
        if (str_contains($localPath, '?')) {
            $localPath = explode('?', $localPath)[0];
        }

        if (file_exists($localPath)) {
            if (false !== $mtime = @filemtime($localPath)) {
                return (string) $mtime;
            }
        }

        return '';
    }

    public function applyVersion(string $path): string
    {
        if ('' !== $version = $this->getVersion($path)) {
            $separator = str_contains($path, '?') ? '&' : '?';

            return \sprintf('%s%sv=%s', $path, $separator, $version);
        }

        return $path;
    }
}
