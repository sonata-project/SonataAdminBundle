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

namespace Sonata\AdminBundle\Twig;

use Doctrine\Common\Util\ClassUtils;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Twig\Extension\RuntimeExtensionInterface;

final class SonataAdminRuntime implements RuntimeExtensionInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
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
            $class = ClassUtils::getClass($model);
            if (!$this->pool->hasAdminByClass($class)) {
                throw new \InvalidArgumentException('You must pass an admin.');
            }

            $admin = $this->pool->getAdminByClass($class);
        }

        return $admin->getUrlSafeIdentifier($model);
    }
}
