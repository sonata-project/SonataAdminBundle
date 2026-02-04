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

namespace SensioLabs\AdminBundle\Twig;

use SensioLabs\AdminBundle\Admin\AdminInterface;
use SensioLabs\AdminBundle\Admin\Pool;
use Twig\Extension\RuntimeExtensionInterface;

final class SensioLabsAdminRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private Pool $pool,
    ) {
    }

    /**
     * Get the identifiers as a string that is safe to use in a url.
     *
     * @return string|null representation of the id that is safe to use in a url
     *
     * @phpstan-template T of object
     * @phpstan-param T $model
     * @phpstan-param AdminInterface<T>|null $admin
     */
    public function getUrlSafeIdentifier(object $model, ?AdminInterface $admin = null): ?string
    {
        if (null === $admin) {
            $class = $model::class;
            if (!$this->pool->hasAdminByClass($class)) {
                throw new \InvalidArgumentException('You must pass an admin.');
            }

            $admin = $this->pool->getAdminByClass($class);
        }

        return $admin->getUrlSafeIdentifier($model);
    }
}
